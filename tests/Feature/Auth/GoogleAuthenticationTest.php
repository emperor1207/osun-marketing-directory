<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;

beforeEach(function () {
    config([
        'services.google.client_id' => 'test-google-client-id',
        'services.google.client_secret' => 'test-google-client-secret',
        'services.google.redirect' => 'http://localhost/auth/google/callback',
    ]);
});

function fakeVerifiedGoogleUser(
    string $id = 'google-account-123',
    string $email = 'google-user@example.com',
    string $name = 'Google User',
    bool $emailVerified = true,
): GoogleUser {
    return GoogleUser::fake([
        'id' => $id,
        'email' => $email,
        'name' => $name,
        'email_verified' => $emailVerified,
    ]);
}

test('customers can register with a verified Google account and chosen username', function () {
    Socialite::fake('google', fakeVerifiedGoogleUser());

    $this->post(route('google.redirect'), [
        'intent' => 'register',
        'username' => '  new-customer  ',
        'role' => 'customer',
    ])->assertRedirect('https://socialite.fake/google/authorize')
        ->assertSessionHas('google_oauth_flow', [
            'intent' => 'register',
            'username' => 'new-customer',
            'role' => 'customer',
        ]);

    $this->get(route('google.callback').'?role=admin')
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'google-user@example.com')->firstOrFail();

    expect($user->name)->toBe('Google User')
        ->and($user->username)->toBe('new-customer')
        ->and($user->role)->toBe('customer')
        ->and($user->google_id)->toBe('google-account-123')
        ->and($user->email_verified_at)->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('business owners can register with a verified Google account', function () {
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-owner-123',
        email: 'google-owner@example.com',
        name: 'Google Business Owner',
    ));

    $this->post(route('google.redirect'), [
        'intent' => 'register',
        'username' => 'osun-business',
        'role' => 'business_owner',
    ])->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'google-owner@example.com')->firstOrFail();

    expect($user->role)->toBe('business_owner')
        ->and($user->username)->toBe('osun-business')
        ->and($user->google_id)->toBe('google-owner-123');

    $this->assertAuthenticatedAs($user);
});

test('verified Google email links an existing account without changing its role', function () {
    $user = User::factory()->create([
        'name' => 'Existing Owner',
        'email' => 'Existing.Owner@example.com',
        'role' => 'business_owner',
    ]);
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-existing-123',
        email: 'existing.owner@example.com',
        name: 'Different Google Name',
    ));

    $this->post(route('google.redirect'), [
        'intent' => 'register',
        'username' => 'unused-google-name',
        'role' => 'business_owner',
    ])->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    expect($user->refresh()->google_id)->toBe('google-existing-123')
        ->and($user->name)->toBe('Existing Owner')
        ->and($user->role)->toBe('business_owner');

    $this->assertAuthenticatedAs($user);
});

test('Google-linked users can sign in directly', function () {
    $user = User::factory()->create();
    $user->forceFill(['google_id' => 'google-linked-123'])->save();
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-linked-123',
        email: 'linked-google@example.com',
    ));

    $this->post(route('google.redirect'), ['intent' => 'login'])
        ->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('Google login does not register an account that is not already linked', function () {
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-login-only-123',
        email: 'login-only@example.com',
    ));

    $this->post(route('google.redirect'), ['intent' => 'login'])
        ->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('users', [
        'email' => 'login-only@example.com',
    ]);
    $this->assertGuest();
});

test('Google cannot link an existing account when its email is unverified', function () {
    $user = User::factory()->create([
        'email' => 'unverified@example.com',
    ]);
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-unverified-123',
        email: 'unverified@example.com',
        emailVerified: false,
    ));

    $this->post(route('google.redirect'), [
        'intent' => 'register',
        'username' => 'unverified-user',
        'role' => 'customer',
    ])->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('username');

    expect($user->refresh()->google_id)->toBeNull();
    $this->assertGuest();
});

test('Google registration does not create an account with an unverified email', function () {
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-unverified-new-123',
        email: 'unverified-new@example.com',
        emailVerified: false,
    ));

    $this->post(route('google.redirect'), [
        'intent' => 'register',
        'username' => 'unverified-new',
        'role' => 'customer',
    ])->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('users', [
        'email' => 'unverified-new@example.com',
    ]);
    $this->assertGuest();
});

test('Google registration rejects an existing account with a different selected role', function () {
    $user = User::factory()->create([
        'email' => 'role-mismatch@example.com',
        'role' => 'customer',
    ]);
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-role-mismatch-123',
        email: 'role-mismatch@example.com',
    ));

    $this->post(route('google.redirect'), [
        'intent' => 'register',
        'username' => 'mismatched-owner',
        'role' => 'business_owner',
    ])->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('role');

    expect($user->refresh()->role)->toBe('customer')
        ->and($user->google_id)->toBeNull();
    $this->assertGuest();
});

test('inactive accounts cannot be linked or signed in with Google', function () {
    $user = User::factory()->create([
        'email' => 'inactive-google@example.com',
        'is_active' => false,
    ]);
    Socialite::fake('google', fakeVerifiedGoogleUser(
        id: 'google-inactive-123',
        email: 'inactive-google@example.com',
    ));

    $this->post(route('google.redirect'), ['intent' => 'login'])
        ->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    expect($user->refresh()->google_id)->toBeNull()
        ->and($user->isActive())->toBeFalse();
    $this->assertGuest();
});

test('Google registration cannot assign the administrator role', function () {
    Socialite::fake('google', fakeVerifiedGoogleUser());

    $this->post(route('google.redirect'), [
        'intent' => 'register',
        'username' => 'google-admin',
        'role' => 'admin',
    ])->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', [
        'email' => 'google-user@example.com',
    ]);
    $this->assertGuest();
});

test('Google callback rejects an administrator role in its server-side flow data', function () {
    Socialite::fake('google', fakeVerifiedGoogleUser());

    $this->withSession([
        'google_oauth_flow' => [
            'intent' => 'register',
            'username' => 'google-admin',
            'role' => 'admin',
        ],
    ])->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('users', [
        'email' => 'google-user@example.com',
    ]);
});

test('Google sign-in handles an OAuth state failure without creating an account', function () {
    Socialite::fake('google', fn () => throw new InvalidStateException);

    $this->post(route('google.redirect'), ['intent' => 'login'])
        ->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('users', [
        'email' => 'google-user@example.com',
    ]);
    $this->assertGuest();
});

test('Google sign-in handles a declined provider authorization', function () {
    Socialite::fake('google', fakeVerifiedGoogleUser());

    $this->post(route('google.redirect'), ['intent' => 'login'])
        ->assertRedirect('https://socialite.fake/google/authorize');

    $this->get(route('google.callback').'?error=access_denied')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});
