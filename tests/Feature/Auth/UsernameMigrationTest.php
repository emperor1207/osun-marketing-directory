<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

test('existing accounts receive unique usernames and remain able to sign in', function () {
    $migration = require database_path('migrations/2026_11_13_154857_add_username_to_users_table.php');
    $migration->down();

    $legacyPassword = Hash::make('legacy-password');
    $legacyAttributes = [
        'name' => 'Legacy Account',
        'password' => $legacyPassword,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    $firstId = DB::table('users')->insertGetId([
        ...$legacyAttributes,
        'email' => 'old.user@example.com',
        'role' => 'customer',
        'is_active' => true,
    ]);
    $secondId = DB::table('users')->insertGetId([
        ...$legacyAttributes,
        'email' => 'old-user@another.example',
        'role' => 'business_owner',
        'is_active' => true,
    ]);

    $migration->up();

    $firstUser = User::query()->findOrFail($firstId);
    $secondUser = User::query()->findOrFail($secondId);

    expect($firstUser->username)->toBe('old-user')
        ->and($secondUser->username)->toBe('old-user-'.$secondId)
        ->and($secondUser->email)->toBe('old-user@another.example')
        ->and($firstUser->role)->toBe('customer')
        ->and($secondUser->role)->toBe('business_owner')
        ->and(Hash::check('legacy-password', $secondUser->password))->toBeTrue();

    $this->post(route('login.store'), [
        'username' => $secondUser->username,
        'password' => 'legacy-password',
    ])->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($secondUser);
});
