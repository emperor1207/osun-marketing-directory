<?php

use App\Models\BusinessProfile;
use App\Models\BusinessReport;
use App\Models\User;

test('customers can report approved listings and view only their own reports', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $anotherCustomer = User::factory()->create(['role' => 'customer']);
    $profile = BusinessProfile::factory()->approved()->create();

    $this->actingAs($customer)
        ->post(route('business-reports.store', $profile), [
            'reason' => 'other',
            'description' => 'The phone number is no longer in service.',
        ])
        ->assertRedirect(route('directory.show', $profile));

    $report = BusinessReport::query()->sole();
    expect($report->customer_id)->toBe($customer->id)
        ->and($report->status)->toBe('submitted');

    $this->get(route('business-reports.mine'))
        ->assertOk()
        ->assertSee($profile->business_name)
        ->assertSee('The phone number is no longer in service.');

    BusinessReport::factory()->create([
        'business_profile_id' => BusinessProfile::factory()->approved(),
        'customer_id' => $anotherCustomer->id,
        'description' => 'Private report details.',
    ]);
    $this->get(route('business-reports.mine'))
        ->assertDontSee('Private report details.');
});

test('report validation, duplicate prevention, approved-profile access and customer authorization are enforced', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $profile = BusinessProfile::factory()->approved()->create();
    $pendingProfile = BusinessProfile::factory()->create(['status' => 'pending']);

    $this->actingAs($customer)
        ->post(route('business-reports.store', $profile), ['reason' => 'other'])
        ->assertSessionHasErrors('description');

    $this->post(route('business-reports.store', $pendingProfile), [
        'reason' => 'closed_business',
    ])->assertNotFound();

    $this->post(route('business-reports.store', $profile), [
        'reason' => 'closed_business',
    ])->assertRedirect(route('directory.show', $profile));

    $this->post(route('business-reports.store', $profile), [
        'reason' => 'closed_business',
    ])->assertSessionHasErrors('reason');

    $owner = User::factory()->create(['role' => 'business_owner']);
    $this->actingAs($owner)
        ->get(route('business-reports.mine'))
        ->assertForbidden();
});
