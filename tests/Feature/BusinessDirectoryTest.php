<?php

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @return array{Category, LocalGovernmentArea, Town}
 */
function createBusinessDirectoryLocation(string $categoryName, string $areaName, string $townName): array
{
    $category = Category::query()->create([
        'name' => $categoryName,
        'slug' => Str::slug($categoryName),
        'is_active' => true,
    ]);

    $localGovernmentArea = LocalGovernmentArea::query()->create([
        'name' => $areaName,
        'slug' => Str::slug($areaName),
        'is_active' => true,
    ]);

    $town = Town::query()->create([
        'name' => $townName,
        'slug' => Str::slug($townName),
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => true,
    ]);

    return [$category, $localGovernmentArea, $town];
}

function createBusinessDirectoryListing(
    Category $category,
    Town $town,
    string $businessName,
    string $status = 'approved',
    array $attributes = []
): BusinessProfile {
    $owner = User::factory()->create(['role' => 'business_owner']);

    return BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => $businessName,
        'slug' => Str::slug($businessName),
        'description' => 'A local Osun State business.',
        'status' => $status,
        ...$attributes,
    ]);
}

test('guests cannot access the business directory but customers can', function () {
    $this->get(route('directory.index'))
        ->assertRedirect(route('login'));

    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.index'))
        ->assertOk();
});

test('directory shows different empty states for an empty catalog and unmatched filters', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.index'))
        ->assertOk()
        ->assertSeeText('No approved businesses have been added to the directory yet. Please check back soon.');

    $this->get(route('directory.index', ['search' => 'no matching business']))
        ->assertOk()
        ->assertSeeText('No approved businesses match these filters. Try changing your search or clearing one of the filters.');
});

test('customers can search and filter the directory by category and location', function () {
    [$category, $localGovernmentArea, $town] = createBusinessDirectoryLocation(
        'Food and Restaurants',
        'Osogbo',
        'Osogbo'
    );
    [$otherCategory, $otherArea, $otherAreaTown] = createBusinessDirectoryLocation(
        'Beauty and Personal Care',
        'Iwo',
        'Iwo'
    );
    $anotherTown = Town::query()->create([
        'name' => 'Ede',
        'slug' => 'ede',
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => true,
    ]);
    $matchingProfile = createBusinessDirectoryListing($category, $town, 'Golden Osogbo Kitchen');
    createBusinessDirectoryListing($otherCategory, $town, 'Golden Other Category');
    createBusinessDirectoryListing($category, $anotherTown, 'Golden Other Town');
    createBusinessDirectoryListing($category, $otherAreaTown, 'Golden Other LGA');
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)->get(route('directory.index', [
        'search' => 'golden',
        'category_id' => $category->id,
        'local_government_area_id' => $localGovernmentArea->id,
        'town_id' => $town->id,
    ]));

    $response->assertOk()
        ->assertSee('Golden Osogbo Kitchen')
        ->assertDontSee('Golden Other Category')
        ->assertDontSee('Golden Other Town')
        ->assertDontSee('Golden Other LGA');
    expect($response->viewData('businessProfiles')->total())->toBe(1);
    expect($response->viewData('businessProfiles')->first()->is($matchingProfile))->toBeTrue();
});

test('only approved businesses appear in results and non-approved detail pages return 404', function () {
    [$category, , $town] = createBusinessDirectoryLocation('Food', 'Osogbo', 'Osogbo');
    $approvedProfile = createBusinessDirectoryListing($category, $town, 'Approved Osogbo Bakery');
    $pendingProfile = createBusinessDirectoryListing($category, $town, 'Pending Osogbo Bakery', 'pending');
    $rejectedProfile = createBusinessDirectoryListing($category, $town, 'Rejected Osogbo Bakery', 'rejected');
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.index'))
        ->assertOk()
        ->assertSee('Approved Osogbo Bakery')
        ->assertDontSee('Pending Osogbo Bakery')
        ->assertDontSee('Rejected Osogbo Bakery');

    $this->get(route('directory.show', $pendingProfile))
        ->assertNotFound();

    $this->get(route('directory.show', $rejectedProfile))
        ->assertNotFound();

    $this->get(route('directory.show', $approvedProfile))
        ->assertOk()
        ->assertSee('Approved Osogbo Bakery');
});

test('business details show contact information and available images', function () {
    [$category, , $town] = createBusinessDirectoryLocation('Food', 'Osogbo', 'Osogbo');
    Storage::fake('public');
    Storage::disk('public')->put('business-profiles/logo/logo.png', 'logo');
    Storage::disk('public')->put('business-profiles/cover_image/cover.png', 'cover');
    $profile = createBusinessDirectoryListing($category, $town, 'Iya Basira Kitchen', 'approved', [
        'description' => 'Fresh meals prepared daily.',
        'street_address' => '12 Oja Oba Road',
        'phone' => '08012345678',
        'whatsapp' => '+2348012345678',
        'email' => 'hello@example.com',
        'website' => 'https://example.com',
        'logo' => 'business-profiles/logo/logo.png',
        'cover_image' => 'business-profiles/cover_image/cover.png',
    ]);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSee('Fresh meals prepared daily.')
        ->assertSee('12 Oja Oba Road')
        ->assertSee('08012345678')
        ->assertSee('+2348012345678')
        ->assertSee('hello@example.com')
        ->assertSee('https://example.com')
        ->assertSee('storage/'.$profile->logo)
        ->assertSee('storage/'.$profile->cover_image);
});

test('missing business profile images are omitted from public details', function () {
    [$category, , $town] = createBusinessDirectoryLocation('Food', 'Osogbo', 'Osogbo');
    Storage::fake('public');
    $profile = createBusinessDirectoryListing($category, $town, 'Iya Basira Kitchen', 'approved', [
        'logo' => 'business-profiles/logo/missing.png',
        'cover_image' => 'business-profiles/cover_image/missing.png',
    ]);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertDontSee('business-profiles/logo/missing.png')
        ->assertDontSee('business-profiles/cover_image/missing.png');
});

test('directory results are paginated and retain filters across pages', function () {
    [$category, , $town] = createBusinessDirectoryLocation('Food', 'Osogbo', 'Osogbo');

    foreach (range(1, 13) as $number) {
        createBusinessDirectoryListing(
            $category,
            $town,
            sprintf('Osogbo Business %02d', $number)
        );
    }

    $customer = User::factory()->create(['role' => 'customer']);

    $firstPage = $this->actingAs($customer)->get(route('directory.index', [
        'category_id' => $category->id,
    ]));

    $firstPage->assertOk()
        ->assertSee('Osogbo Business 01')
        ->assertDontSee('Osogbo Business 13')
        ->assertSee('category_id='.$category->id);
    expect($firstPage->viewData('businessProfiles')->total())->toBe(13);

    $this->get(route('directory.index', [
        'category_id' => $category->id,
        'page' => 2,
    ]))
        ->assertOk()
        ->assertSee('Osogbo Business 13');
});
