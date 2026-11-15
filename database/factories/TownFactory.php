<?php

namespace Database\Factories;

use App\Models\LocalGovernmentArea;
use App\Models\Town;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Town>
 */
class TownFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'slug' => fake()->unique()->slug(),
            'local_government_area_id' => LocalGovernmentArea::factory(),
            'is_active' => true,
        ];
    }
}
