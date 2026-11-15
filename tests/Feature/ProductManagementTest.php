<?php

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Product;
use App\Models\Town;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function createProductManagementProfile(User $owner, string $status = 'approved'): BusinessProfile
{
    $suffix = Str::lower((string) Str::uuid());
    $category = Category::query()->create([
        'name' => 'Category '.$suffix,
        'slug' => 'category-'.$suffix,
        'is_active' => true,
    ]);
    $localGovernmentArea = LocalGovernmentArea::query()->create([
        'name' => 'LGA '.$suffix,
        'slug' => 'lga-'.$suffix,
        'is_active' => true,
    ]);
    $town = Town::query()->create([
        'name' => 'Town '.$suffix,
        'slug' => 'town-'.$suffix,
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => true,
    ]);

    return BusinessProfile::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'town_id' => $town->id,
        'business_name' => 'Business '.$suffix,
        'slug' => 'business-'.$suffix,
        'status' => $status,
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function productManagementFormData(array $overrides = []): array
{
    return array_replace([
        'name' => 'Osun Honey',
        'description' => 'Locally produced honey.',
        'price' => '2500.00',
        'item_type' => 'product',
        'is_available' => '1',
    ], $overrides);
}

test('business owners can create products and services without changing profile approval', function () {
    Storage::fake('public');
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = createProductManagementProfile($owner);

    $this->actingAs($owner)
        ->post(route('business-products.store'), [
            ...productManagementFormData(),
            'image' => UploadedFile::fake()->image('honey.webp'),
            'business_profile_id' => 99999,
        ])
        ->assertRedirect(route('business-products.index'))
        ->assertSessionHas('status', 'Product or service created.');

    $product = $profile->products()->firstOrFail();
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'business_profile_id' => $profile->id,
        'name' => 'Osun Honey',
        'price' => '2500.00',
        'item_type' => 'product',
        'is_available' => true,
    ]);
    Storage::disk('public')->assertExists($product->image);
    expect($profile->refresh()->status)->toBe('approved');

    $this->post(route('business-products.store'), productManagementFormData([
        'name' => 'Catering',
        'item_type' => 'service',
        'price' => '15000.00',
        'image' => null,
    ]))
        ->assertRedirect(route('business-products.index'));

    $this->assertDatabaseHas('products', [
        'business_profile_id' => $profile->id,
        'name' => 'Catering',
        'item_type' => 'service',
        'price' => '15000.00',
    ]);
});

test('business owners can edit their own item and replace its image', function () {
    Storage::fake('public');
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = createProductManagementProfile($owner);
    $oldImage = UploadedFile::fake()->image('old.jpg')->storePublicly('business-items', 'public');
    $product = $profile->products()->create([
        ...productManagementFormData(),
        'image' => $oldImage,
    ]);

    $this->actingAs($owner)
        ->put(route('business-products.update', $product), productManagementFormData([
            'name' => 'Updated Osun Honey',
            'price' => '3000.00',
            'item_type' => 'service',
            'is_available' => '0',
            'image' => UploadedFile::fake()->image('new.png'),
        ]))
        ->assertRedirect(route('business-products.index'))
        ->assertSessionHas('status', 'Product or service updated.');

    $product->refresh();
    expect($product->name)->toBe('Updated Osun Honey')
        ->and($product->price)->toBe('3000.00')
        ->and($product->item_type)->toBe('service')
        ->and($product->is_available)->toBeFalse()
        ->and($product->image)->not->toBe($oldImage);

    Storage::disk('public')->assertMissing($oldImage);
    Storage::disk('public')->assertExists($product->image);
    expect($profile->refresh()->status)->toBe('approved');
});

test('business owners can delete their own item and its stored image', function () {
    Storage::fake('public');
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = createProductManagementProfile($owner);
    $image = UploadedFile::fake()->image('item.jpg')->storePublicly('business-items', 'public');
    $product = $profile->products()->create([
        ...productManagementFormData(),
        'image' => $image,
    ]);

    $this->actingAs($owner)
        ->delete(route('business-products.destroy', $product))
        ->assertRedirect(route('business-products.index'))
        ->assertSessionHas('status', 'Product or service deleted.');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    Storage::disk('public')->assertMissing($image);
    expect($profile->refresh()->status)->toBe('approved');
});

test('business owners cannot view edit or delete another owners item', function () {
    Storage::fake('public');
    $owner = User::factory()->create(['role' => 'business_owner']);
    $otherOwner = User::factory()->create(['role' => 'business_owner']);
    createProductManagementProfile($owner);
    $otherProfile = createProductManagementProfile($otherOwner);
    $image = UploadedFile::fake()->image('private.jpg')->storePublicly('business-items', 'public');
    $otherProduct = $otherProfile->products()->create([
        ...productManagementFormData(),
        'image' => $image,
    ]);

    $this->actingAs($owner)
        ->get(route('business-products.edit', $otherProduct))
        ->assertNotFound();

    $this->put(route('business-products.update', $otherProduct), productManagementFormData([
        'name' => 'Unauthorized change',
    ]))->assertNotFound();

    $this->delete(route('business-products.destroy', $otherProduct))
        ->assertNotFound();

    $this->assertDatabaseHas('products', [
        'id' => $otherProduct->id,
        'business_profile_id' => $otherProfile->id,
        'name' => 'Osun Honey',
    ]);
    Storage::disk('public')->assertExists($image);
});

test('customers and administrators cannot access or create owner items', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);
    createProductManagementProfile($owner);

    $this->get(route('business-products.index'))
        ->assertRedirect(route('login'));

    foreach (['customer', 'admin'] as $role) {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get(route('business-products.index'))
            ->assertForbidden();

        $this->post(route('business-products.store'), productManagementFormData())
            ->assertForbidden();
    }

    $this->assertDatabaseCount('products', 0);
});

test('product prices must be non-negative and have at most two decimal places', function (string $price) {
    $owner = User::factory()->create(['role' => 'business_owner']);
    createProductManagementProfile($owner);

    $this->actingAs($owner)
        ->post(route('business-products.store'), productManagementFormData(['price' => $price]))
        ->assertSessionHasErrors('price');

    $this->assertDatabaseCount('products', 0);
})->with([
    'negative price' => '-0.01',
    'too many decimal places' => '12.345',
]);

test('product images with unsupported content are rejected', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);
    createProductManagementProfile($owner);

    $this->actingAs($owner)
        ->post(route('business-products.store'), productManagementFormData([
            'image' => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf'),
        ]))
        ->assertSessionHasErrors('image');

    $this->assertDatabaseCount('products', 0);
});

test('approved business details display products and services', function () {
    Storage::fake('public');
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = createProductManagementProfile($owner);
    $productImage = UploadedFile::fake()->image('honey.jpg')->storePublicly('business-items', 'public');

    Product::factory()->forBusinessProfile($profile)->create([
        'name' => 'Osun Honey',
        'description' => 'Fresh local honey.',
        'price' => '2500.00',
        'item_type' => 'product',
        'image' => $productImage,
    ]);
    Product::factory()->forBusinessProfile($profile)->create([
        'name' => 'Catering',
        'description' => 'Event catering service.',
        'price' => '15000.00',
        'item_type' => 'service',
    ]);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSeeText('Products & Services')
        ->assertSeeText('Osun Honey')
        ->assertSeeText('Fresh local honey.')
        ->assertSeeText('Product')
        ->assertSeeText('₦2,500.00')
        ->assertSee('storage/'.$productImage)
        ->assertSeeText('Catering')
        ->assertSeeText('Service')
        ->assertSeeText('₦15,000.00');
});

test('products for pending and rejected profiles are not publicly exposed', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    foreach (['pending', 'rejected'] as $status) {
        $owner = User::factory()->create(['role' => 'business_owner']);
        $profile = createProductManagementProfile($owner, $status);
        Product::factory()->forBusinessProfile($profile)->create([
            'name' => ucfirst($status).' private offering',
        ]);

        $this->actingAs($customer)
            ->get(route('directory.show', $profile))
            ->assertNotFound();
    }

    $this->actingAs($customer)
        ->get(route('directory.index'))
        ->assertOk()
        ->assertDontSee('Pending private offering')
        ->assertDontSee('Rejected private offering');
});

test('product and service empty states render for owners and customers', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = createProductManagementProfile($owner);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($owner)
        ->get(route('business-products.index'))
        ->assertOk()
        ->assertSeeText('No products or services yet');

    $this->actingAs($customer)
        ->get(route('directory.show', $profile))
        ->assertOk()
        ->assertSeeText('No products or services listed yet');
});

test('owners can update or delete items when their previous image file is missing', function () {
    Storage::fake('public');
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = createProductManagementProfile($owner);
    $product = $profile->products()->create([
        ...productManagementFormData(),
        'image' => 'business-items/deleted-image.jpg',
    ]);

    $this->actingAs($owner)
        ->put(route('business-products.update', $product), [
            ...productManagementFormData(['name' => 'Updated item']),
            'image' => UploadedFile::fake()->image('replacement.jpg'),
        ])
        ->assertRedirect(route('business-products.index'));

    $product->refresh();
    expect($product->name)->toBe('Updated item')
        ->and($product->image)->not->toBe('business-items/deleted-image.jpg');
    Storage::disk('public')->assertExists($product->image);

    $product->update(['image' => 'business-items/another-deleted-image.jpg']);
    $this->delete(route('business-products.destroy', $product))
        ->assertRedirect(route('business-products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('administrators can inspect products and services on business profile reviews', function () {
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = createProductManagementProfile($owner, 'pending');
    Product::factory()->forBusinessProfile($profile)->create([
        'name' => 'Admin-visible offering',
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.profiles.show', $profile))
        ->assertOk()
        ->assertSeeText('Products & Services')
        ->assertSeeText('Admin-visible offering');
});
