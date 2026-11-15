<?php

use App\Models\BusinessProfile;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;

test('administrators can hide and restore reviews and business responses', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = BusinessProfile::factory()->approved()->create();
    $review = Review::factory()->create(['business_profile_id' => $profile->id]);
    $reply = ReviewReply::factory()->create([
        'review_id' => $review->id,
        'business_owner_id' => $profile->user_id,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.reviews.update', $review), ['status' => 'hidden'])
        ->assertRedirect(route('admin.reviews.show', $review));
    expect($review->refresh()->status)->toBe('hidden')
        ->and($review->moderated_by)->toBe($admin->id);

    $this->get(route('directory.show', $profile))->assertDontSee($review->content);

    $this->patch(route('admin.review-replies.update', $reply), ['status' => 'hidden'])
        ->assertRedirect(route('admin.reviews.show', $review));
    expect($reply->refresh()->status)->toBe('hidden');

    $this->patch(route('admin.reviews.update', $review), ['status' => 'visible']);
    $this->patch(route('admin.review-replies.update', $reply), ['status' => 'visible']);
    $this->get(route('directory.show', $profile))
        ->assertSee($review->content)
        ->assertSee($reply->content);
});

test('administrators can delete reviews and replies while nonadministrators are forbidden', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = BusinessProfile::factory()->approved()->create();
    $review = Review::factory()->create(['business_profile_id' => $profile->id]);
    $reply = ReviewReply::factory()->create([
        'review_id' => $review->id,
        'business_owner_id' => $profile->user_id,
    ]);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('admin.reviews.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('admin.review-replies.destroy', $reply))
        ->assertRedirect(route('admin.reviews.show', $review));
    $this->delete(route('admin.reviews.destroy', $review))
        ->assertRedirect(route('admin.reviews.index'));

    $this->assertDatabaseMissing('review_replies', ['id' => $reply->id]);
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});
