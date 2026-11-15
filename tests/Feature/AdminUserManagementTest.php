<?php

use App\Models\User;

test('administrators can change account roles and activation status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($admin)
        ->put(route('admin.users.role.update', $user), ['role' => 'business_owner'])
        ->assertRedirect(route('admin.users.show', $user));
    expect($user->refresh()->role)->toBe('business_owner');

    $this->patch(route('admin.users.status.update', $user), ['is_active' => '0'])
        ->assertRedirect(route('admin.users.show', $user));
    expect($user->refresh()->is_active)->toBeFalse();

    $this->get(route('admin.users.index', ['status' => 'inactive']))
        ->assertOk()
        ->assertSee($user->name);
});

test('administrators cannot change their own account and may safely demote another administrator', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->put(route('admin.users.role.update', $admin), ['role' => 'customer'])
        ->assertForbidden();
    $this->patch(route('admin.users.status.update', $admin), ['is_active' => '0'])
        ->assertForbidden();

    $this->put(route('admin.users.role.update', $user), ['role' => 'customer'])
        ->assertRedirect(route('admin.users.show', $user));
    expect($user->refresh()->role)->toBe('customer')
        ->and($admin->refresh()->role)->toBe('admin')
        ->and($admin->is_active)->toBeTrue();
});

test('customers and business owners cannot access administrator account management', function () {
    foreach (['customer', 'business_owner'] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
});

test('account search treats injection-shaped input as a literal filter value', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create([
        'name' => 'Secret customer account',
        'role' => 'customer',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => "' OR 1=1 --"]))
        ->assertOk()
        ->assertDontSee('Secret customer account');
});
