<?php

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @return array{Category, LocalGovernmentArea, Town}
 */
function createBusinessProfileReferenceData(): array
{
    $category = Category::query()->create([
        'name' => 'Food and Restaurants',
        'slug' => 'food-and-restaurants',
        'is_active' => true,
    ]);

    $localGovernmentArea = LocalGovernmentArea::query()->create([
        'name' => 'Osogbo',
        'slug' => 'osogbo',
        'is_active' => true,
    ]);

    $town = Town::query()->create([
        'name' => 'Osogbo',
        'slug' => 'osogbo',
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => true,
    ]);

    return [$category, $localGovernmentArea, $town];
}

/**
 * @return array{
 *     business_name: string,
 *     category_id: int,
 *     local_government_area_id: int,
 *     town_id: int,
 *     street_address: string,
 *     description: string,
 *     phone: string,
 *     whatsapp: string,
 *     email: string,
 *     website: string
 * }
 */
function businessProfileFormData(Category $category, LocalGovernmentArea $localGovernmentArea, Town $town): array
{
    return [
        'business_name' => 'Iya Basira Kitchen',
        'category_id' => $category->id,
        'local_government_area_id' => $localGovernmentArea->id,
        'town_id' => $town->id,
        'street_address' => '12 Oja Oba Road',
        'description' => 'Local meals in Osogbo.',
        'phone' => '08012345678',
        'whatsapp' => '08012345678',
        'email' => 'hello@example.com',
        'website' => 'https://example.com',
    ];
}

test('business owners must be signed in to manage profiles', function () {
    $this->get(route('business-profile.show'))
        ->assertRedirect(route('login'));

    $owner = User::factory()->create(['role' => 'business_owner']);

    $this->actingAs($owner)
        ->get(route('business-profile.create'))
        ->assertOk();
});

test('customers cannot access business profile management', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('business-profile.create'))
        ->assertForbidden();
});

test('business owners can create one pending profile and view it', function () {
    [$category, $localGovernmentArea, $town] = createBusinessProfileReferenceData();
    $owner = User::factory()->create(['role' => 'business_owner']);
    $formData = businessProfileFormData($category, $localGovernmentArea, $town);
    Storage::fake('public');

    $response = $this->actingAs($owner)->post(route('business-profile.store'), [
        ...$formData,
        'status' => 'approved',
        'logo' => UploadedFile::fake()->image('logo.png'),
        'cover_image' => UploadedFile::fake()->image('cover.png'),
    ]);

    $response->assertRedirect(route('business-profile.show'));
    $businessProfile = $owner->businessProfile()->firstOrFail();
    $this->assertDatabaseHas('business_profiles', [
        'user_id' => $owner->id,
        'business_name' => 'Iya Basira Kitchen',
        'category_id' => $category->id,
        'town_id' => $town->id,
        'status' => 'pending',
    ]);
    Storage::disk('public')->assertExists($businessProfile->logo);
    Storage::disk('public')->assertExists($businessProfile->cover_image);

    $this->get(route('business-profile.show'))
        ->assertOk()
        ->assertSee('Iya Basira Kitchen')
        ->assertSee('Osogbo');
});

test('business owners cannot select a town outside the selected LGA', function () {
    [$category, $localGovernmentArea, $town] = createBusinessProfileReferenceData();
    $anotherArea = LocalGovernmentArea::query()->create([
        'name' => 'Iwo',
        'slug' => 'iwo',
        'is_active' => true,
    ]);
    $anotherTown = Town::query()->create([
        'name' => 'Iwo',
        'slug' => 'iwo',
        'local_government_area_id' => $anotherArea->id,
        'is_active' => true,
    ]);
    $owner = User::factory()->create(['role' => 'business_owner']);

    $response = $this->actingAs($owner)->post(route('business-profile.store'), [
        ...businessProfileFormData($category, $localGovernmentArea, $town),
        'town_id' => $anotherTown->id,
    ]);

    $response->assertSessionHasErrors('town_id');
    $this->assertDatabaseCount('business_profiles', 0);
});

test('editing an approved profile returns it to review and ignores submitted moderation fields', function () {
    [$category, $localGovernmentArea, $town] = createBusinessProfileReferenceData();
    $owner = User::factory()->create(['role' => 'business_owner']);
    $businessProfile = BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => 'Old Business Name',
        'slug' => 'old-business-name',
        'status' => 'approved',
        'approved_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($owner)->put(route('business-profile.update'), [
        ...businessProfileFormData($category, $localGovernmentArea, $town),
        'status' => 'rejected',
        'approved_at' => now()->subDays(2)->toDateTimeString(),
        'rejection_reason' => 'Owner-supplied moderation data',
    ]);

    $response->assertRedirect(route('business-profile.show'));
    $this->assertDatabaseHas('business_profiles', [
        'id' => $businessProfile->id,
        'business_name' => 'Iya Basira Kitchen',
        'status' => 'pending',
        'approved_at' => null,
        'rejection_reason' => null,
    ]);
});

test('editing a rejected profile resubmits it for review', function () {
    [$category, $localGovernmentArea, $town] = createBusinessProfileReferenceData();
    $owner = User::factory()->create(['role' => 'business_owner']);
    $businessProfile = BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => 'Rejected Business',
        'slug' => 'rejected-business',
        'status' => 'rejected',
        'rejection_reason' => 'Please correct the business details.',
    ]);

    $response = $this->actingAs($owner)->put(route('business-profile.update'), [
        ...businessProfileFormData($category, $localGovernmentArea, $town),
    ]);

    $response->assertRedirect(route('business-profile.show'));
    $this->assertDatabaseHas('business_profiles', [
        'id' => $businessProfile->id,
        'business_name' => 'Iya Basira Kitchen',
        'status' => 'pending',
        'approved_at' => null,
        'rejection_reason' => null,
    ]);
});

test('business owners cannot create a second profile', function () {
    [$category, $localGovernmentArea, $town] = createBusinessProfileReferenceData();
    $owner = User::factory()->create(['role' => 'business_owner']);
    BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => 'Existing Business',
        'slug' => 'existing-business',
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->post(route('business-profile.store'), businessProfileFormData($category, $localGovernmentArea, $town))
        ->assertConflict();

    $this->assertDatabaseCount('business_profiles', 1);
});

test('business owners without a profile are sent to the create form', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);

    $this->actingAs($owner)
        ->get(route('business-profile.show'))
        ->assertRedirect(route('business-profile.create'));

    $this->get(route('business-profile.create'))
        ->assertOk()
        ->assertSee('Create your business profile')
        ->assertSee('Business category')
        ->assertSee('Local government area');
});
