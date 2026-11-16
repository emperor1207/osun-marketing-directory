<?php

use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** @return array{0: User, 1: BusinessProfile} */
function galleryTestOwner(): array
{
    $owner = User::factory()->create(['role' => 'business_owner']);
    $profile = BusinessProfile::factory()->approved()->create(['user_id' => $owner->id]);

    return [$owner, $profile];
}

/** @return array<string, mixed> */
function galleryTestForm(array $extra = []): array
{
    return array_replace(['name' => 'Osun Honey', 'price' => '2500.00', 'item_type' => 'product'], $extra);
}

test('owners can add a product with a main image and several extra photos', function () {
    Storage::fake('public');
    [$owner] = galleryTestOwner();

    $this->actingAs($owner)->post(route('business-products.store'), galleryTestForm([
        'image' => UploadedFile::fake()->image('main.jpg'),
        'gallery' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
    ]))->assertRedirect(route('business-products.index'));

    $product = Product::query()->firstOrFail();

    expect($product->images)->toHaveCount(2)->and($product->galleryUrls())->toHaveCount(3);
    foreach ($product->images as $photo) {
        Storage::disk('public')->assertExists($photo->path);
    }
});

test('owners can remove extra photos and the files are deleted', function () {
    Storage::fake('public');
    [$owner, $profile] = galleryTestOwner();
    $product = Product::factory()->forBusinessProfile($profile)->create();
    $keep = $product->images()->create(['path' => 'business-items/gallery/keep.jpg', 'sort_order' => 0]);
    $drop = $product->images()->create(['path' => 'business-items/gallery/drop.jpg', 'sort_order' => 1]);
    Storage::disk('public')->put($drop->path, 'x');

    $this->actingAs($owner)
        ->put(route('business-products.update', $product), galleryTestForm(['remove_gallery' => [$drop->id]]))
        ->assertRedirect(route('business-products.index'));

    expect($product->images()->pluck('id')->all())->toBe([$keep->id]);
    Storage::disk('public')->assertMissing($drop->path);
});

test('a product cannot have more than six extra photos', function () {
    Storage::fake('public');
    [$owner, $profile] = galleryTestOwner();
    $product = Product::factory()->forBusinessProfile($profile)->create();

    foreach (range(1, 5) as $number) {
        $product->images()->create(['path' => "business-items/gallery/p{$number}.jpg", 'sort_order' => $number]);
    }

    $this->actingAs($owner)
        ->put(route('business-products.update', $product), galleryTestForm([
            'gallery' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ]))
        ->assertSessionHasErrors('gallery');

    expect($product->images()->count())->toBe(5);
});

test('an owner cannot remove photos that belong to another business', function () {
    Storage::fake('public');
    [$owner, $profile] = galleryTestOwner();
    $ownProduct = Product::factory()->forBusinessProfile($profile)->create();
    [, $otherProfile] = galleryTestOwner();
    $otherProduct = Product::factory()->forBusinessProfile($otherProfile)->create();
    $foreignPhoto = $otherProduct->images()->create(['path' => 'business-items/gallery/foreign.jpg', 'sort_order' => 0]);

    $this->actingAs($owner)
        ->put(route('business-products.update', $ownProduct), galleryTestForm(['remove_gallery' => [$foreignPhoto->id]]))
        ->assertRedirect();

    expect($otherProduct->images()->count())->toBe(1);
});

test('deleting a product also deletes its photos', function () {
    Storage::fake('public');
    [$owner, $profile] = galleryTestOwner();
    $product = Product::factory()->forBusinessProfile($profile)->create();
    $photo = $product->images()->create(['path' => 'business-items/gallery/gone.jpg', 'sort_order' => 0]);
    Storage::disk('public')->put($photo->path, 'x');

    $this->actingAs($owner)->delete(route('business-products.destroy', $product))->assertRedirect();

    Storage::disk('public')->assertMissing($photo->path);
    $this->assertDatabaseMissing('product_images', ['id' => $photo->id]);
});
