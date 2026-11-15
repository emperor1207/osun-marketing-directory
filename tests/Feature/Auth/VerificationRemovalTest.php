<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('registration signs the user in without sending verification email', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Customer',
        'username' => 'customer',
        'email' => 'new-customer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'new-customer@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertOk();
    Notification::assertNothingSent();
});

test('email verification and OTP endpoints are not registered', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/email/verify')
        ->assertNotFound();

    $this->get('/email/verify-otp')->assertNotFound();
    $this->post('/email/verify-otp')->assertNotFound();
    $this->post('/email/verify-otp/resend')->assertNotFound();
    $this->post('/email/verification-notification')->assertNotFound();
});
