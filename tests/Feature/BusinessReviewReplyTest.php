<?php

use App\Models\BusinessProfile;
use App\Models\Review;
use App\Models\User;

test('business owners can respond to reviews on their listing and manage their response', function () {
    $profile = BusinessProfile::factory()->approved()->create();
    $owner = $profile->owner;
    $review = Review::factory()->create(['business_profile_id' => $profile->id]);

    $this->actingAs($owner)
        ->put(route('business-reviews.reply', $review), ['content' => 'Thank you for visiting.'])
        ->assertRedirect(route('business-reviews.index'));

    expect($review->reply()->sole()->content)->toBe('Thank you for visiting.');

    $this->put(route('business-reviews.reply', $review), ['content' => 'We appreciate your feedback.'])
        ->assertRedirect(route('business-reviews.index'));
    expect($review->reply()->sole()->content)->toBe('We appreciate your feedback.');

    $this->delete(route('business-reviews.reply.destroy', $review))
        ->assertRedirect(route('business-reviews.index'));
    expect($review->reply()->exists())->toBeFalse();
});

test('business owners cannot respond to another listing and other roles cannot manage replies', function () {
    $owner = BusinessProfile::factory()->approved()->create()->owner;
    $otherProfile = BusinessProfile::factory()->approved()->create();
    $review = Review::factory()->create(['business_profile_id' => $otherProfile->id]);

    $this->actingAs($owner)
        ->put(route('business-reviews.reply', $review), ['content' => 'Unauthorized response.'])
        ->assertNotFound();

    foreach (['customer', 'admin'] as $role) {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user)
            ->get(route('business-reviews.index'))
            ->assertForbidden();
    }

    expect($review->reply()->exists())->toBeFalse();
});
