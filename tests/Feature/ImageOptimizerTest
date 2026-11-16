<?php

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('large photos are resized to fit the limit', function () {
    Storage::fake('public');

    $path = app(ImageOptimizer::class)->store(UploadedFile::fake()->image('big.jpg', 3000, 2000), 'business-items', 1280);

    Storage::disk('public')->assertExists($path);
    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));

    expect(max($width, $height))->toBeLessThanOrEqual(1280)->and($width)->toBeGreaterThan($height);
});

test('small photos are stored without changes', function () {
    Storage::fake('public');

    $path = app(ImageOptimizer::class)->store(UploadedFile::fake()->image('small.jpg', 400, 300), 'business-items');

    Storage::disk('public')->assertExists($path);
    [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));

    expect([$width, $height])->toBe([400, 300])->and($path)->not->toEndWith('.webp');
});
