<?php

use App\Models\Category;
use App\Models\LocalGovernmentArea;
use App\Models\Town;
use Database\Seeders\DatabaseSeeder;

test('default seeder populates catalogs in dependency order and can be rerun', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBe(17)
        ->and(LocalGovernmentArea::query()->count())->toBe(30)
        ->and(Town::query()->count())->toBe(126);

    $this->assertDatabaseMissing('users', ['role' => 'admin']);

    $this->seed(DatabaseSeeder::class);

    expect(Category::query()->count())->toBe(17)
        ->and(LocalGovernmentArea::query()->count())->toBe(30)
        ->and(Town::query()->count())->toBe(126);
});

test('default seeder preserves existing catalog edits', function () {
    $category = Category::query()->create([
        'name' => 'Agriculture and Farming',
        'slug' => 'custom-agriculture',
        'description' => 'A locally maintained description.',
        'is_active' => false,
    ]);

    $localGovernmentArea = LocalGovernmentArea::query()->create([
        'name' => 'Aiyedaade',
        'slug' => 'custom-aiyedaade',
        'headquarters' => 'Locally maintained headquarters',
        'is_active' => false,
    ]);

    $town = Town::query()->create([
        'name' => 'Gbongan',
        'slug' => 'custom-gbongan',
        'local_government_area_id' => $localGovernmentArea->id,
        'is_active' => false,
    ]);

    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'slug' => 'custom-agriculture',
        'description' => 'A locally maintained description.',
        'is_active' => false,
    ]);
    $this->assertDatabaseHas('local_government_areas', [
        'id' => $localGovernmentArea->id,
        'slug' => 'custom-aiyedaade',
        'headquarters' => 'Locally maintained headquarters',
        'is_active' => false,
    ]);
    $this->assertDatabaseHas('towns', [
        'id' => $town->id,
        'slug' => 'custom-gbongan',
        'is_active' => false,
    ]);
});
