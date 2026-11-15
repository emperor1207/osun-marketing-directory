<?php

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use App\Models\User;
use Illuminate\Support\Str;

test('homepage introduces the directory and links search to approved business categories', function () {
    $category = Category::query()->create([
        'name' => 'Food and Restaurants',
        'slug' => 'food-and-restaurants',
        'is_active' => true,
    ]);
    $pendingCategory = Category::query()->create([
        'name' => 'Pending Listings',
        'slug' => 'pending-listings',
        'is_active' => true,
    ]);
    $inactiveCategory = Category::query()->create([
        'name' => 'Inactive Category',
        'slug' => 'inactive-category',
        'is_active' => false,
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

    foreach ([
        [$category, 'Approved Bakery', 'approved'],
        [$pendingCategory, 'Pending Bakery', 'pending'],
        [$inactiveCategory, 'Inactive Bakery', 'approved'],
    ] as [$listingCategory, $businessName, $status]) {
        $owner = User::factory()->create(['role' => 'business_owner']);

        BusinessProfile::query()->create([
            'user_id' => $owner->id,
            'category_id' => $listingCategory->id,
            'town_id' => $town->id,
            'business_name' => $businessName,
            'slug' => Str::slug($businessName),
            'description' => 'A local Osun State business.',
            'status' => $status,
        ]);
    }

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeText('Osun Marketing Directory')
        ->assertSeeText('Find local businesses and services across Osun State.')
        ->assertSeeText('Home')
        ->assertSeeText('Directory')
        ->assertSeeText('Login')
        ->assertSeeText('Register')
        ->assertSeeText('Discover businesses')
        ->assertSeeText('Register your business')
        ->assertSee('name="search"', false)
        ->assertSee('action="'.route('directory.index').'"', false)
        ->assertSee(route('login'), false)
        ->assertSee(route('register'), false)
        ->assertSee(route('directory.index', ['category_id' => $category->id]), false)
        ->assertSeeText('Food and Restaurants')
        ->assertSeeText('Approved Bakery')
        ->assertDontSeeText('Inactive Bakery')
        ->assertDontSeeText('Pending Listings')
        ->assertDontSeeText('Inactive Category');

    expect($response->viewData('localGovernmentAreaCount'))->toBe(1)
        ->and($response->viewData('recentBusinesses'))->toHaveCount(1);
});

test('homepage shows a real empty state when no businesses are approved', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('0')
        ->assertSeeText('No approved business listings are available yet.');
});
