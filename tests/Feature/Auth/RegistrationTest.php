<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk()
        ->assertSeeText('Osun Marketing Directory')
        ->assertSeeText('Register - Osun Marketing Directory')
        ->assertSeeText('Continue with Google');
});

test('new users can register', function () {
    Notification::fake();

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'username' => 'john-doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->get(route('dashboard'))->assertOk();
    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    $this->assertNull($user->email_verified_at);
    expect($user->routeNotificationFor('mail'))->toBe('test@example.com');
    Notification::assertNothingSent();
});

test('business owners can register without email verification', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'New Business Owner',
        'username' => 'business-owner',
        'email' => 'owner@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'business_owner',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'owner@example.com',
        'role' => 'business_owner',
        'email_verified_at' => null,
    ]);
});

test('public registration cannot create an administrator account', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Public Admin Attempt',
        'username' => 'public-admin',
        'email' => 'public-admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertDatabaseMissing('users', [
        'email' => 'public-admin@example.com',
    ]);
});

test('registration stores a normalized username', function () {
    $this->post(route('register.store'), [
        'name' => 'Case Normalization',
        'username' => '  Mixed_Case-User  ',
        'email' => 'normalized@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', [
        'email' => 'normalized@example.com',
        'username' => 'mixed_case-user',
    ]);
});

test('registration rejects usernames that differ only by letter case', function () {
    User::factory()->create(['username' => 'taken-name']);

    $this->post(route('register.store'), [
        'name' => 'Duplicate Username',
        'username' => 'TAKEN-NAME',
        'email' => 'duplicate-username@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('users', [
        'email' => 'duplicate-username@example.com',
    ]);
});

test('registration requires a username', function () {
    $this->post(route('register.store'), [
        'name' => 'Missing Username',
        'email' => 'missing-username@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('users', [
        'email' => 'missing-username@example.com',
    ]);
});
