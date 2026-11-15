<?php

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use App\Models\User;
use Illuminate\Support\Str;

function createAdminTestBusinessProfile(string $businessName, string $status): BusinessProfile
{
    $category = Category::query()->create([
        'name' => $businessName.' category',
        'slug' => Str::slug($businessName.' category'),
        'is_active' => true,
    ]);
    $localGovernmentArea = LocalGovernmentArea::query()->create([
        'name' => $businessName.' LGA',
        'slug' => Str::slug($businessName.' LGA'),
        'is_active' => true,
    ]);
    $town = Town::query()->create([
        'name' => $businessName.' town',
        'slug' => Str::slug($businessName.' town'),
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => true,
    ]);
    $owner = User::factory()->create(['role' => 'business_owner']);

    return BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => $businessName,
        'slug' => Str::slug($businessName),
        'description' => 'A local business for administrator review.',
        'status' => $status,
    ]);
}

test('guests cannot access administration but administrators can', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('customers and business owners cannot access administration', function () {
    foreach (['customer', 'business_owner'] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
});

test('administrators see dashboard statistics and pending profiles', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'customer']);
    createAdminTestBusinessProfile('Pending Osun Business', 'pending');
    createAdminTestBusinessProfile('Approved Osun Business', 'approved');
    createAdminTestBusinessProfile('Rejected Osun Business', 'rejected');

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Pending Osun Business')
        ->assertDontSee('Approved Osun Business')
        ->assertDontSee('Rejected Osun Business');
    expect($response->viewData('statistics'))->toMatchArray([
        'users' => 5,
        'customers' => 1,
        'business_owners' => 3,
        'business_profiles' => 3,
        'pending_profiles' => 1,
        'approved_profiles' => 1,
        'rejected_profiles' => 1,
    ]);
});

test('administrators can browse approved and rejected profiles', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $approvedProfile = createAdminTestBusinessProfile('Approved Directory Business', 'approved');
    $rejectedProfile = createAdminTestBusinessProfile('Rejected Directory Business', 'rejected');

    $this->actingAs($admin)
        ->get(route('admin.profiles.index', ['status' => 'approved']))
        ->assertOk()
        ->assertSee('Approved Directory Business')
        ->assertDontSee('Rejected Directory Business');

    $this->get(route('admin.profiles.show', $rejectedProfile))
        ->assertOk()
        ->assertSee('Rejected Directory Business');

    $this->get(route('admin.profiles.show', $approvedProfile))
        ->assertOk()
        ->assertSee('Approved Directory Business');
});

test('administrators can approve a pending profile and its approval time is recorded', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = createAdminTestBusinessProfile('Approval Test Business', 'pending');
    $this->travelTo('2026-01-02 03:04:05');

    $this->actingAs($admin)
        ->post(route('admin.profiles.approve', $profile))
        ->assertRedirect(route('admin.profiles.show', $profile))
        ->assertSessionHas('status', 'The business profile has been approved.');

    $profile->refresh();
    expect($profile->status)->toBe('approved')
        ->and($profile->approved_at?->format('Y-m-d H:i:s'))->toBe('2026-01-02 03:04:05');
    $this->assertDatabaseHas('business_profiles', [
        'id' => $profile->id,
        'status' => 'approved',
        'rejection_reason' => null,
    ]);
});

test('administrators cannot approve a profile that is not pending', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = createAdminTestBusinessProfile('Already Approved Business', 'approved');

    $this->actingAs($admin)
        ->post(route('admin.profiles.approve', $profile))
        ->assertConflict();

    expect($profile->refresh()->status)->toBe('approved');
});

test('administrators must provide a rejection reason and can reject a pending profile', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = createAdminTestBusinessProfile('Rejection Test Business', 'pending');

    $this->actingAs($admin)
        ->post(route('admin.profiles.reject', $profile), ['rejection_reason' => ''])
        ->assertSessionHasErrors([
            'rejection_reason' => 'The rejection reason field is required.',
        ]);
    expect($profile->refresh()->status)->toBe('pending');

    $this->post(route('admin.profiles.reject', $profile), [
        'rejection_reason' => 'Please provide a visible street address.',
    ])
        ->assertRedirect(route('admin.profiles.show', $profile))
        ->assertSessionHas('status', 'The business profile has been rejected.');

    $this->assertDatabaseHas('business_profiles', [
        'id' => $profile->id,
        'status' => 'rejected',
        'approved_at' => null,
        'rejection_reason' => 'Please provide a visible street address.',
    ]);
});

test('administrators can create and update categories without deleting existing records', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::query()->create([
        'name' => 'Original Category',
        'slug' => 'original-category',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.categories'))
        ->assertOk()
        ->assertSee('Business categories')
        ->assertSee('Original Category');

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Home Services',
            'description' => 'Services for local households.',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.categories'))
        ->assertSessionHas('status', 'Category created.');

    $this->assertDatabaseHas('categories', [
        'name' => 'Home Services',
        'slug' => 'home-services',
        'is_active' => true,
    ]);

    $this->put(route('admin.categories.update', $category), [
        'name' => 'Updated Category',
        'description' => 'Updated description.',
    ])
        ->assertRedirect(route('admin.categories'))
        ->assertSessionHas('status', 'Category updated.');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Category',
        'slug' => 'updated-category',
        'is_active' => false,
    ]);
});

test('administrators can create towns in different LGAs while rejecting duplicate names in one LGA', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $firstArea = LocalGovernmentArea::query()->create([
        'name' => 'First LGA',
        'slug' => 'first-lga',
        'is_active' => true,
    ]);
    $secondArea = LocalGovernmentArea::query()->create([
        'name' => 'Second LGA',
        'slug' => 'second-lga',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.towns.store'), [
            'name' => 'Central',
            'local_government_area_id' => $firstArea->id,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.locations'))
        ->assertSessionHas('status', 'Town created.');

    $this->post(route('admin.towns.store'), [
        'name' => 'Central',
        'local_government_area_id' => $firstArea->id,
    ])->assertSessionHasErrors([
        'name' => 'This town already exists in the selected local government area.',
    ]);

    $this->post(route('admin.towns.store'), [
        'name' => 'Central',
        'local_government_area_id' => $secondArea->id,
    ])
        ->assertRedirect(route('admin.locations'))
        ->assertSessionHas('status', 'Town created.');

    $this->assertDatabaseHas('towns', [
        'name' => 'Central',
        'slug' => 'central-2',
        'local_government_area_id' => $secondArea->id,
    ]);
});

test('administrators can create and update local government areas and towns', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.local-government-areas.store'), [
            'name' => 'Ife East',
            'headquarters' => 'Oke Ogbo',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.locations'))
        ->assertSessionHas('status', 'Local government area created.');

    $localGovernmentArea = LocalGovernmentArea::query()->where('name', 'Ife East')->firstOrFail();
    $town = Town::query()->create([
        'name' => 'Oke Ogbo',
        'slug' => 'oke-ogbo',
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.locations'))
        ->assertOk()
        ->assertSee('Local government areas and towns')
        ->assertSee('Ife East')
        ->assertSee('Oke Ogbo');

    $this->put(route('admin.local-government-areas.update', $localGovernmentArea), [
        'name' => 'Ife East Updated',
        'headquarters' => 'Ife',
    ])
        ->assertRedirect(route('admin.locations'))
        ->assertSessionHas('status', 'Local government area updated.');

    $this->put(route('admin.towns.update', $town), [
        'name' => 'New Oke Ogbo',
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => '1',
    ])
        ->assertRedirect(route('admin.locations'))
        ->assertSessionHas('status', 'Town updated.');

    $this->assertDatabaseHas('local_government_areas', [
        'id' => $localGovernmentArea->id,
        'name' => 'Ife East Updated',
        'headquarters' => 'Ife',
        'is_active' => false,
    ]);
    $this->assertDatabaseHas('towns', [
        'id' => $town->id,
        'name' => 'New Oke Ogbo',
        'slug' => 'new-oke-ogbo',
        'is_active' => true,
    ]);
});
