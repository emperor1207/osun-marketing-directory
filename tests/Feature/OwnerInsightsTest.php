<?php

use App\Models\BusinessProfile;
use App\Models\BusinessProfileView;
use App\Models\Favourite;
use App\Models\Review;
use App\Models\User;

test('owners see visitor, rating and saved counts on their profile page', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = BusinessProfile::factory()->approved()->create(['user_id' => $owner->id]);

    foreach ([['a', 2], ['b', 10], ['c', 40]] as [$key, $daysAgo]) {
        BusinessProfileView::query()->create([
            'business_profile_id' => $profile->id,
            'visitor_key' => $key,
            'viewed_at' => now()->subDays($daysAgo),
        ]);
    }

    Review::factory()->create(['business_profile_id' => $profile->id, 'rating' => 4]);
    Review::factory()->create(['business_profile_id' => $profile->id, 'rating' => 5]);
    Favourite::query()->create(['user_id' => User::factory()->create()->id, 'business_profile_id' => $profile->id]);

    $response = $this->actingAs($owner)->get(route('business-profile.show'))->assertOk()->assertSee('Business insights');
    $analytics = $response->viewData('analytics');

    expect($analytics['total_visitors'])->toBe(3)
        ->and($analytics['last_7_days'])->toBe(1)
        ->and($analytics['last_30_days'])->toBe(2)
        ->and($analytics['reviews_count'])->toBe(2)
        ->and((float) $analytics['average_rating'])->toBe(4.5)
        ->and($analytics['saved_by'])->toBe(1)
        ->and($analytics['daily'])->toHaveCount(14);
});
