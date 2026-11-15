<?php

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use App\Models\User;
use Illuminate\Support\Str;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('customers can visit the dashboard and browse the directory', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk()
        ->assertSeeText('Customer')
        ->assertSeeText('Browse the business directory')
        ->assertSeeText('No approved businesses have been added to the directory yet.')
        ->assertSeeText('Categories will appear as approved businesses are added')
        ->assertSee(route('directory.index'), false)
        ->assertDontSee(route('admin.dashboard'), false)
        ->assertDontSee(route('business-profile.show'), false)
        ->assertDontSee('laravel/livewire-starter-kit')
        ->assertDontSee('laravel.com/docs/starter-kits');
});

test('business owners see their listing status and management links on the dashboard', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);
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
    BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => 'Osogbo Bakery',
        'slug' => Str::slug('Osogbo Bakery'),
        'description' => 'A local Osun State business.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($owner)->get(route('dashboard'));

    $response->assertOk()
        ->assertSeeText('Business owner')
        ->assertSeeText('Osogbo Bakery')
        ->assertSeeText('Pending')
        ->assertSee(route('business-profile.show'), false)
        ->assertSee(route('business-profile.edit'), false);
});

test('customers see categories and recently approved businesses from directory data', function () {
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
    $owner = User::factory()->create(['role' => 'business_owner']);
    $businessProfile = BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => 'Approved Osogbo Bakery',
        'slug' => 'approved-osogbo-bakery',
        'description' => 'Freshly baked goods in Osogbo.',
        'status' => 'approved',
    ]);
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)->get(route('dashboard'));

    $response->assertOk()
        ->assertSeeText('1')
        ->assertSeeText('Food and Restaurants')
        ->assertSeeText('Approved Osogbo Bakery')
        ->assertSeeText('Osogbo, Osogbo')
        ->assertSee(route('directory.index', ['category_id' => $category->id]), false)
        ->assertSee(route('directory.show', $businessProfile), false);
    expect($response->viewData('recentBusinesses')->first()->is($businessProfile))->toBeTrue();
});

test('administrators see links to the administration dashboard and catalog management', function () {
    $administrator = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($administrator)->get(route('dashboard'));

    $response->assertOk()
        ->assertSeeText('Administrator')
        ->assertSee(route('admin.dashboard'), false)
        ->assertSee(route('admin.categories'), false)
        ->assertSee(route('admin.locations'), false)
        ->assertDontSee(route('business-profile.show'), false);
});
