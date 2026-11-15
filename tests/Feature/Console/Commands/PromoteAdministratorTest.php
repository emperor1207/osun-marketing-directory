<?php

use App\Models\User;

test('operators can promote an existing account to an active administrator', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'is_active' => false,
    ]);

    $this->artisan('app:promote-admin', ['email' => $user->email])
        ->expectsOutput($user->email.' is now an active administrator.')
        ->assertExitCode(0);

    expect($user->fresh()->role)->toBe('admin')
        ->and($user->fresh()->isActive())->toBeTrue();
});

test('admin promotion fails explicitly when the account does not exist', function () {
    $this->artisan('app:promote-admin', ['email' => 'missing@example.com'])
        ->expectsOutput('No account exists with that email address.')
        ->assertExitCode(1);
});
