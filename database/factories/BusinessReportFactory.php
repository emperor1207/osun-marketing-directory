<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\BusinessReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessReport>
 */
class BusinessReportFactory extends Factory
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
            'reason' => fake()->randomElement([
                'incorrect_information',
                'misleading_listing',
                'inappropriate_content',
                'duplicate_listing',
                'closed_business',
                'other',
            ]),
            'description' => fake()->optional()->sentence(),
            'status' => 'submitted',
        ];
    }
}
