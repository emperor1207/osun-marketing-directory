<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_profile_id' => BusinessProfile::factory()->approved(),
            'customer_id' => User::factory()->state(['role' => 'customer']),
            'rating' => fake()->numberBetween(1, 5),
            'content' => fake()->paragraph(),
            'status' => 'visible',
        ];
    }
}
