<?php

use App\Models\BusinessProfile;
use App\Models\User;
use App\Notifications\BusinessProfileApproved;
use App\Notifications\BusinessProfileRejected;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Facades\Notification;

test('the owner is emailed when an administrator approves the profile', function () {
    Notification::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = BusinessProfile::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)->post(route('admin.profiles.approve', $profile))->assertRedirect();

    Notification::assertSentTo($profile->owner, BusinessProfileApproved::class);
    Notification::assertNotSentTo($profile->owner, BusinessProfileRejected::class);
});

test('the owner is emailed the reason when an administrator rejects the profile', function () {
    Notification::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = BusinessProfile::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)
        ->post(route('admin.profiles.reject', $profile), ['rejection_reason' => 'Please add a working phone number.'])
        ->assertRedirect();

    Notification::assertSentTo(
        $profile->owner,
        BusinessProfileRejected::class,
        fn (BusinessProfileRejected $notification) => $notification->businessProfile->rejection_reason === 'Please add a working phone number.'
    );
});

test('no email is sent when the decision is not allowed', function () {
    Notification::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = BusinessProfile::factory()->approved()->create();

    $this->actingAs($admin)->post(route('admin.profiles.approve', $profile))->assertStatus(409);

    Notification::assertNothingSent();
});

test('the decision is saved even when the email cannot be delivered', function () {
    $this->mock(Dispatcher::class, function ($mock) {
        $mock->shouldReceive('send')->andThrow(new RuntimeException('Mail server unavailable'));
    });
    $admin = User::factory()->create(['role' => 'admin']);
    $profile = BusinessProfile::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)
        ->post(route('admin.profiles.approve', $profile))
        ->assertRedirect(route('admin.profiles.show', $profile))
        ->assertSessionHas('status', 'The business profile has been approved.');

    expect($profile->fresh()->status)->toBe('approved');
});

test('the emails contain the business name, the reason and a link', function () {
    $profile = BusinessProfile::factory()->create([
        'business_name' => 'Glow Touch Salon',
        'status' => 'rejected',
        'rejection_reason' => 'Add a clearer description.',
    ]);

    $rejected = (string) (new BusinessProfileRejected($profile))->toMail($profile->owner)->render();
    $approved = (string) (new BusinessProfileApproved($profile))->toMail($profile->owner)->render();

    expect($rejected)->toContain('Glow Touch Salon')->toContain('Add a clearer description.')->toContain(route('business-profile.edit'))
        ->and($approved)->toContain('Glow Touch Salon')->toContain(route('directory.show', $profile));
});
