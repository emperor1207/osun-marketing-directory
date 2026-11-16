<?php

use App\Models\BusinessProfile;
use App\Models\BusinessProfileView;
use App\Models\Review;
use App\Models\User;

function directoryNames($response): array
{
    return $response->viewData('businessProfiles')->pluck('business_name')->all();
}

test('businesses can be sorted by rating with unrated ones last', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $low = BusinessProfile::factory()->approved()->create(['business_name' => 'Alpha Low']);
    $high = BusinessProfile::factory()->approved()->create(['business_name' => 'Bravo High']);
    BusinessProfile::factory()->approved()->create(['business_name' => 'Charlie Unrated']);
    Review::factory()->create(['business_profile_id' => $low->id, 'rating' => 2]);
    Review::factory()->create(['business_profile_id' => $high->id, 'rating' => 5]);

    $response = $this->actingAs($customer)->get(route('directory.index', ['sort' => 'rating']))->assertOk();

    expect(directoryNames($response))->toBe(['Bravo High', 'Alpha Low', 'Charlie Unrated']);
});

test('businesses can be sorted by newest and by most visited', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $old = BusinessProfile::factory()->approved()->create(['business_name' => 'Alpha Old', 'created_at' => now()->subDays(10)]);
    $new = BusinessProfile::factory()->approved()->create(['business_name' => 'Zulu New', 'created_at' => now()]);
    BusinessProfileView::query()->create(['business_profile_id' => $old->id, 'visitor_key' => 'a', 'viewed_at' => now()]);
    BusinessProfileView::query()->create(['business_profile_id' => $old->id, 'visitor_key' => 'b', 'viewed_at' => now()]);

    $newest = $this->actingAs($customer)->get(route('directory.index', ['sort' => 'newest']));
    $popular = $this->actingAs($customer)->get(route('directory.index', ['sort' => 'popular']));

    expect(directoryNames($newest))->toBe(['Zulu New', 'Alpha Old'])
        ->and(directoryNames($popular))->toBe(['Alpha Old', 'Zulu New']);
    $this->actingAs($customer)->get(route('directory.index', ['sort' => 'nonsense']))->assertSessionHasErrors('sort');
});

test('the business page has call, whatsapp, directions and map', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $profile = BusinessProfile::factory()->approved()->create([
        'phone' => '08012345678',
        'whatsapp' => '08012345678',
        'latitude' => 7.7719,
        'longitude' => 4.5560,
    ]);

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSee('tel:08012345678', false)
        ->assertSee('https://wa.me/2348012345678', false)
        ->assertSee('Get directions')
        ->assertSee('openstreetmap.org/export/embed.html', false);
});

test('the map is hidden when the business has no coordinates', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $profile = BusinessProfile::factory()->approved()->create(['latitude' => null, 'longitude' => null]);

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSee('Get directions')
        ->assertDontSee('openstreetmap.org/export/embed.html', false);
});
