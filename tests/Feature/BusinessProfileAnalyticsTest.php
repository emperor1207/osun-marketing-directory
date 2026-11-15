<?php

use App\Models\BusinessProfile;
use App\Models\BusinessProfileView;
use App\Models\Review;
use App\Models\User;

test('directory profile views are counted once per visitor session and exclude owners and administrators', function () {
    $profile = BusinessProfile::factory()->approved()->create();
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)->get(route('directory.show', $profile));
    $response->assertOk();
    $sessionCookie = $response->getCookie(config('session.cookie'));
    $this->withCookie(config('session.cookie'), $sessionCookie->getValue())
        ->get(route('directory.show', $profile))
        ->assertOk();
    expect($profile->views()->count())->toBe(1);

    $this->actingAs($profile->owner)->get(route('directory.show', $profile))->assertOk();
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('directory.show', $profile))->assertOk();
    expect($profile->views()->count())->toBe(1);
});

test('business-owner dashboard displays visible review ratings and profile view statistics', function () {
    $profile = BusinessProfile::factory()->approved()->create();
    Review::factory()->create([
        'business_profile_id' => $profile->id,
        'rating' => 4,
        'content' => 'Great service.',
        'status' => 'visible',
    ]);
    Review::factory()->create([
        'business_profile_id' => $profile->id,
        'rating' => 1,
        'content' => 'Hidden feedback.',
        'status' => 'hidden',
    ]);
    BusinessProfileView::factory()->count(2)->create([
        'business_profile_id' => $profile->id,
        'viewed_at' => now()->subDays(3),
    ]);

    $response = $this->actingAs($profile->owner)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Visible reviews')
        ->assertSee('Average rating')
        ->assertSee('Profile views')
        ->assertSee('Recent reviews')
        ->assertSee('Manage reviews and responses')
        ->assertSee('Response needed');
    expect($response->viewData('ownerReviewCount'))->toBe(1)
        ->and((float) $response->viewData('ownerAverageRating'))->toBe(4.0)
        ->and($response->viewData('ownerProfileViewCount'))->toBe(2)
        ->and($response->viewData('ownerRecentViewCount'))->toBe(2);
});
