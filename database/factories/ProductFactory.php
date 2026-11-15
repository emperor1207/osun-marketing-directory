<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 0, 500000),
            'image' => null,
            'item_type' => fake()->randomElement(['product', 'service']),
            'is_available' => true,
        ];
    }

    public function forBusinessProfile(BusinessProfile $businessProfile): static
    {
        return $this->state(fn (): array => [
            'business_profile_id' => $businessProfile->getKey(),
        ]);
    }
}
