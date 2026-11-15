<?php

use App\Models\BusinessProfile;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;

test('customers can submit a review and edit or delete their own review', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $profile = BusinessProfile::factory()->approved()->create();

    $this->actingAs($customer)
        ->post(route('reviews.store', $profile), [
            'rating' => 5,
            'content' => 'Excellent local service.',
        ])
        ->assertRedirect(route('directory.show', $profile))
        ->assertSessionHas('status', 'Your review has been submitted.');

    $review = Review::query()->sole();
    expect($review->customer_id)->toBe($customer->id)
        ->and($review->business_profile_id)->toBe($profile->id);

    $this->put(route('reviews.update', $review), [
        'rating' => 4,
        'content' => 'Updated feedback.',
    ])
        ->assertRedirect(route('directory.show', $profile))
        ->assertSessionHas('status', 'Your review has been updated.');

    expect($review->refresh()->rating)->toBe(4)
        ->and($review->content)->toBe('Updated feedback.');

    $this->delete(route('reviews.destroy', $review))
        ->assertRedirect(route('directory.show', $profile));
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

test('customers cannot submit duplicate reviews or invalid ratings', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $profile = BusinessProfile::factory()->approved()->create();
    Review::factory()->create([
        'business_profile_id' => $profile->id,
        'customer_id' => $customer->id,
    ]);

    $this->actingAs($customer)
        ->post(route('reviews.store', $profile), [
            'rating' => 5,
            'content' => 'A duplicate.',
        ])
        ->assertSessionHasErrors('content');

    $anotherProfile = BusinessProfile::factory()->approved()->create();
    $this->post(route('reviews.store', $anotherProfile), [
        'rating' => 6,
        'content' => 'Invalid rating.',
    ])->assertSessionHasErrors('rating');

    expect(Review::query()->count())->toBe(1);
});

test('only customers can review approved listings and customers cannot change another review', function () {
    $profile = BusinessProfile::factory()->approved()->create();
    $pendingProfile = BusinessProfile::factory()->create(['status' => 'pending']);
    $review = Review::factory()->create(['business_profile_id' => $profile->id]);
    $anotherCustomer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($anotherCustomer)
        ->put(route('reviews.update', $review), [
            'rating' => 1,
            'content' => 'Unauthorized edit.',
        ])
        ->assertNotFound();

    $this->delete(route('reviews.destroy', $review))->assertNotFound();

    $this->post(route('reviews.store', $pendingProfile), [
        'rating' => 5,
        'content' => 'Pending listing review.',
    ])->assertNotFound();

    foreach (['business_owner', 'admin'] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->post(route('reviews.store', $profile), [
                'rating' => 5,
                'content' => 'Not a customer.',
            ])
            ->assertForbidden();
    }

    expect($review->refresh()->content)->not->toBe('Unauthorized edit.');
});

test('customer reviews and business replies escape user-provided HTML', function () {
    $profile = BusinessProfile::factory()->approved()->create();
    $review = Review::factory()->create([
        'business_profile_id' => $profile->id,
        'content' => '<script>alert("review")</script>',
    ]);
    ReviewReply::factory()->create([
        'review_id' => $review->id,
        'business_owner_id' => $profile->user_id,
        'content' => '<img src=x onerror=alert(1)>',
    ]);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSee('&lt;script&gt;', false)
        ->assertSee('&lt;img', false)
        ->assertDontSee('<script>alert("review")</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false);
});
