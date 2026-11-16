<?php

use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake(config('filesystems.media_disk'));
});

test('demo seeder adds approved, pending and rejected businesses with products and reviews', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DemoDataSeeder::class);

    expect(BusinessProfile::query()->where('status', 'approved')->count())->toBe(27)
        ->and(BusinessProfile::query()->where('status', 'pending')->count())->toBe(2)
        ->and(BusinessProfile::query()->where('status', 'rejected')->count())->toBe(1)
        ->and(Product::query()->count())->toBe(98)
        ->and(Product::query()->where('item_type', 'product')->exists())->toBeTrue()
        ->and(Product::query()->where('item_type', 'service')->exists())->toBeTrue()
        ->and(Review::query()->count())->toBe(34)
        ->and(User::query()->where('role', 'admin')->exists())->toBeFalse();
});

test('demo businesses have logos, covers, whatsapp numbers and photo galleries', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DemoDataSeeder::class);
    $disk = Storage::disk(config('filesystems.media_disk'));

    $salon = BusinessProfile::query()->where('business_name', 'Glow Touch Salon')->firstOrFail();
    $item = Product::query()->where('business_profile_id', $salon->id)->where('name', 'Hair braiding')->firstOrFail();

    expect($salon->whatsapp)->not->toBeNull()
        ->and($salon->latitude)->not->toBeNull()
        ->and($item->images)->toHaveCount(2)
        ->and(ProductImage::query()->count())->toBe(112)
        ->and($item->galleryUrls())->toHaveCount(3);

    $disk->assertExists($salon->logo);
    $disk->assertExists($salon->cover_image);
    $disk->assertExists($item->image);
});

test('demo seeder can be run again without duplicating records', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DemoDataSeeder::class);
    $counts = [BusinessProfile::count(), Product::count(), ProductImage::count(), Review::count(), User::count()];

    $this->seed(DemoDataSeeder::class);

    expect([BusinessProfile::count(), Product::count(), ProductImage::count(), Review::count(), User::count()])->toBe($counts);
});

test('demo businesses can be found by searching for a product or service', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DemoDataSeeder::class);
    $customer = User::query()->where('email', 'customer1@demo.osunmarketing.test')->firstOrFail();

    $this->actingAs($customer)
        ->get(route('directory.index', ['search' => 'braiding']))
        ->assertOk()
        ->assertSee('Glow Touch Salon')
        ->assertDontSee('Ede Fresh Bakery');

    $this->actingAs($customer)
        ->get(route('directory.index', ['search' => 'screen']))
        ->assertOk()
        ->assertSee('Iwo Tech Repair Hub');
});
