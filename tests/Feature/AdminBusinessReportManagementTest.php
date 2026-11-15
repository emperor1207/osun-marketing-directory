<?php

use App\Models\BusinessProfile;
use App\Models\BusinessReport;
use App\Models\User;

test('administrators can resolve reports with a resolution note', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $report = BusinessReport::factory()->create([
        'business_profile_id' => BusinessProfile::factory()->approved(),
        'reason' => 'incorrect_information',
        'description' => 'The address needs correction.',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.business-reports.update', $report), [
            'status' => 'resolved',
            'resolution_note' => 'The owner updated the address.',
        ])
        ->assertRedirect(route('admin.business-reports.show', $report));

    expect($report->refresh()->status)->toBe('resolved')
        ->and($report->resolution_note)->toBe('The owner updated the address.')
        ->and($report->handled_by)->toBe($admin->id);
});

test('report resolution requires a note and nonadministrators cannot manage reports', function () {
    $report = BusinessReport::factory()->create([
        'business_profile_id' => BusinessProfile::factory()->approved(),
    ]);
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($admin)
        ->patch(route('admin.business-reports.update', $report), [
            'status' => 'resolved',
            'resolution_note' => '',
        ])
        ->assertSessionHasErrors('resolution_note');
    expect($report->refresh()->status)->toBe('submitted');

    $this->actingAs($customer)
        ->get(route('admin.business-reports.index'))
        ->assertForbidden();
});
