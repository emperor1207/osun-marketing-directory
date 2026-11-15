<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertSeeText('Continue with Google')
        ->assertSeeText('Username');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can log in with usernames regardless of letter case', function () {
    $user = User::factory()->create(['username' => 'case-sensitive-name']);

    $this->post(route('login.store'), [
        'username' => 'CASE-SENSITIVE-NAME',
        'password' => 'password',
    ])->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users can log in without email verification and access the dashboard', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $response = $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertOk();
});

test('deactivated users cannot authenticate', function () {
    $user = User::factory()->create(['is_active' => false]);

    $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('username');

    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('username');

    $this->assertGuest();
});

test('users cannot log in with an email address instead of their username', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'username' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('username');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
