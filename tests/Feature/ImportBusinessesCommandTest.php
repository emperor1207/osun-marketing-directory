<?php

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Product;
use App\Models\Town;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function importCsv(array $rows): string
{
    $header = 'business_name,category,town,owner_name,owner_email,phone,whatsapp,street_address,description,website,latitude,longitude,items';
    $path = tempnam(sys_get_temp_dir(), 'biz');
    file_put_contents($path, $header."\n".implode("\n", $rows)."\n");

    return $path;
}

beforeEach(function () {
    Storage::fake('local');
    Category::factory()->create(['name' => 'Health and Wellness']);
    Town::factory()->create(['name' => 'Ede']);
});

test('the importer creates a pending owner account, profile and listings', function () {
    $csv = importCsv(['"De-Gideon Pharmacy",Health and Wellness,Ede,Dr Gideon,,08000000001,,"4 Test Road",Pharmacy,,,,"Vitamin C:product:1500; BP check:service:500"']);

    $this->artisan('app:import-businesses', ['file' => $csv])
        ->expectsOutputToContain('Created: 1 | Skipped: 0 | Invalid: 0')
        ->assertSuccessful();

    $profile = BusinessProfile::query()->where('business_name', 'De-Gideon Pharmacy')->firstOrFail();
    expect($profile->status)->toBe('pending')
        ->and($profile->whatsapp)->toBe('08000000001')
        ->and($profile->owner->role)->toBe('business_owner')
        ->and(Product::query()->where('business_profile_id', $profile->id)->count())->toBe(2)
        ->and(Product::query()->where('name', 'BP check')->value('item_type'))->toBe('service');
});

test('the saved credentials file contains a working password', function () {
    $this->artisan('app:import-businesses', ['file' => importCsv(['Abeke Pharmacy,Health and Wellness,Ede,,,,,,,,,,'])])->assertSuccessful();

    $files = Storage::disk('local')->allFiles('imports');
    expect($files)->toHaveCount(1);

    $lines = array_map('str_getcsv', explode("\n", trim(Storage::disk('local')->get($files[0]))));
    $owner = User::query()->where('email', $lines[1][2])->firstOrFail();

    expect(Hash::check($lines[1][3], $owner->password))->toBeTrue();
});

test('the approve option publishes the business immediately', function () {
    $this->artisan('app:import-businesses', ['file' => importCsv(['Bright Mall,Health and Wellness,Ede,,,,,,,,,,']), '--approve' => true])->assertSuccessful();

    $profile = BusinessProfile::query()->firstOrFail();
    expect($profile->status)->toBe('approved')->and($profile->approved_at)->not->toBeNull();
});

test('a dry run saves nothing', function () {
    $this->artisan('app:import-businesses', ['file' => importCsv(['Merry Mart,Health and Wellness,Ede,,,,,,,,,,']), '--dry-run' => true])
        ->expectsOutputToContain('Dry run: nothing was saved.')
        ->assertSuccessful();

    expect(BusinessProfile::count())->toBe(0)->and(User::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('imports'))->toBeEmpty();
});

test('invalid rows are reported and valid rows are still imported', function () {
    $csv = importCsv([
        'Good Business,Health and Wellness,Ede,,,,,,,,,,',
        'Wrong Category,Space Travel,Ede,,,,,,,,,,',
        'Wrong Town,Health and Wellness,Atlantis,,,,,,,,,,',
        'Bad Price,Health and Wellness,Ede,,,,,,,,,,"Thing:product:cheap"',
    ]);

    $this->artisan('app:import-businesses', ['file' => $csv])
        ->expectsOutputToContain('Created: 1 | Skipped: 0 | Invalid: 3')
        ->assertSuccessful();

    expect(BusinessProfile::pluck('business_name')->all())->toBe(['Good Business']);
});

test('running the same file twice does not create duplicates', function () {
    $csv = importCsv(['Dotman Laundry,Health and Wellness,Ede,,,,,,,,,,']);

    $this->artisan('app:import-businesses', ['file' => $csv])->assertSuccessful();
    $this->artisan('app:import-businesses', ['file' => $csv])
        ->expectsOutputToContain('Created: 0 | Skipped: 1 | Invalid: 0')
        ->assertSuccessful();

    expect(BusinessProfile::count())->toBe(1)->and(User::count())->toBe(1);
});
