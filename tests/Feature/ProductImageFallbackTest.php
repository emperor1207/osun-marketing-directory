<?php

use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('public business details show a fallback for missing product images and an empty state without items', function () {
    Storage::fake('public');
    $profile = BusinessProfile::factory()->approved()->create();
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSeeText('No products or services listed yet');

    Product::factory()->create([
        'business_profile_id' => $profile->id,
        'image' => 'business-items/missing.jpg',
        'name' => 'Unavailable Image Item',
    ]);

    $this->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSee('Unavailable Image Item image unavailable', false)
        ->assertSeeText('Image unavailable')
        ->assertSeeText('Available');
});
