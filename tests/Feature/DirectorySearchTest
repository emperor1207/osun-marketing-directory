<?php

use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\Town;
use App\Models\User;

function searchableBusiness(string $name, array $attributes = []): BusinessProfile
{
    return BusinessProfile::factory()->approved()->create([
        'business_name' => $name,
        'description' => null,
        ...$attributes,
    ]);
}

function searchDirectory(array $query)
{
    $customer = User::factory()->create(['role' => 'customer']);

    return test()->actingAs($customer)->get(route('directory.index', $query));
}

test('customers can find a business by a product it sells', function () {
    $bakery = searchableBusiness('Sunrise Stores');
    searchableBusiness('Unrelated Traders');
    Product::factory()->forBusinessProfile($bakery)->create(['name' => 'Fresh Bread', 'item_type' => 'product']);

    $response = searchDirectory(['search' => 'bread']);

    $response->assertOk()->assertSee('Sunrise Stores')->assertDontSee('Unrelated Traders');
    expect($response->viewData('businessProfiles')->total())->toBe(1);
});

test('customers can find a business by a service description', function () {
    $salon = searchableBusiness('Glow Studio');
    Product::factory()->forBusinessProfile($salon)->create([
        'name' => 'Bridal package',
        'description' => 'Professional hair braiding at your home',
        'item_type' => 'service',
    ]);

    searchDirectory(['search' => 'braiding'])->assertOk()->assertSee('Glow Studio');
});

test('unavailable products and unapproved businesses never appear in search', function () {
    $hidden = searchableBusiness('Paused Shop');
    Product::factory()->forBusinessProfile($hidden)->create(['name' => 'Gadget', 'is_available' => false]);

    $pending = BusinessProfile::factory()->create(['business_name' => 'Pending Shop', 'description' => null]);
    Product::factory()->forBusinessProfile($pending)->create(['name' => 'Gadget']);

    $response = searchDirectory(['search' => 'gadget']);

    $response->assertOk()->assertDontSee('Paused Shop')->assertDontSee('Pending Shop');
    expect($response->viewData('businessProfiles')->total())->toBe(0);
});

test('every word in the search must match', function () {
    $ede = Town::factory()->create(['name' => 'Ede']);
    $osogbo = Town::factory()->create(['name' => 'Osogbo']);
    $edeBusiness = searchableBusiness('Ede Fashion House', ['town_id' => $ede->id]);
    $osogboBusiness = searchableBusiness('Osogbo Fashion House', ['town_id' => $osogbo->id]);
    Product::factory()->forBusinessProfile($edeBusiness)->create(['name' => 'Tailoring']);
    Product::factory()->forBusinessProfile($osogboBusiness)->create(['name' => 'Tailoring']);

    $response = searchDirectory(['search' => 'tailoring osogbo']);

    expect($response->viewData('businessProfiles')->total())->toBe(1);
    expect($response->viewData('businessProfiles')->first()->is($osogboBusiness))->toBeTrue();
});

test('the type filter separates products from services', function () {
    $shop = searchableBusiness('Repair Hub');
    $onlyServices = searchableBusiness('Fix It Fast');
    Product::factory()->forBusinessProfile($shop)->create(['name' => 'Phone case', 'item_type' => 'product']);
    Product::factory()->forBusinessProfile($onlyServices)->create(['name' => 'Screen repair', 'item_type' => 'service']);

    $products = searchDirectory(['item_type' => 'product']);
    $services = searchDirectory(['item_type' => 'service']);

    expect($products->viewData('businessProfiles')->pluck('id')->all())->toBe([$shop->id]);
    expect($services->viewData('businessProfiles')->pluck('id')->all())->toBe([$onlyServices->id]);
    searchDirectory(['item_type' => 'invalid'])->assertSessionHasErrors('item_type');
});

test('percent and underscore characters are searched literally', function () {
    searchableBusiness('Plain Business');

    $response = searchDirectory(['search' => '%']);

    expect($response->viewData('businessProfiles')->total())->toBe(0);
});
