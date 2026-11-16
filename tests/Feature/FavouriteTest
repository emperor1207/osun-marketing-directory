<?php

use App\Models\BusinessProfile;
use App\Models\User;

test('customers can save and unsave an approved business', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $profile = BusinessProfile::factory()->approved()->create();

    $this->actingAs($customer)->post(route('favourites.toggle', $profile))->assertRedirect();
    $this->assertDatabaseHas('favourites', ['user_id' => $customer->id, 'business_profile_id' => $profile->id]);

    $this->actingAs($customer)->post(route('favourites.toggle', $profile))->assertRedirect();
    $this->assertDatabaseMissing('favourites', ['user_id' => $customer->id, 'business_profile_id' => $profile->id]);
});

test('businesses that are not approved cannot be saved', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $pending = BusinessProfile::factory()->create(['status' => 'pending']);

    $this->actingAs($customer)->post(route('favourites.toggle', $pending))->assertNotFound();
});

test('owners cannot save their own business', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = BusinessProfile::factory()->approved()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->post(route('favourites.toggle', $profile))->assertForbidden();
});

test('the saved businesses page lists only the businesses the user saved', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $saved = BusinessProfile::factory()->approved()->create(['business_name' => 'Saved Shop']);
    BusinessProfile::factory()->approved()->create(['business_name' => 'Other Shop']);

    $this->actingAs($customer)->post(route('favourites.toggle', $saved));

    $this->actingAs($customer)
        ->get(route('favourites.index'))
        ->assertOk()
        ->assertSee('Saved Shop')
        ->assertDontSee('Other Shop');
});

test('guests cannot open the saved businesses page', function () {
    $this->get(route('favourites.index'))->assertRedirect();
});
