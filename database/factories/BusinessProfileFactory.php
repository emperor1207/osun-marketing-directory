<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Town;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessProfile>
 */
class BusinessProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'business_owner']),
            'category_id' => Category::factory(),
            'town_id' => Town::factory(),
            'business_name' => fake()->company(),
            'slug' => Str::slug(fake()->unique()->company()),
            'description' => fake()->optional()->paragraph(),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}
