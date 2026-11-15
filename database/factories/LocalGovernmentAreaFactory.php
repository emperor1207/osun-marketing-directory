<?php

namespace Database\Factories;

use App\Models\LocalGovernmentArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocalGovernmentArea>
 */
class LocalGovernmentAreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'slug' => fake()->unique()->slug(),
            'headquarters' => fake()->optional()->city(),
            'is_active' => true,
        ];
    }
}
