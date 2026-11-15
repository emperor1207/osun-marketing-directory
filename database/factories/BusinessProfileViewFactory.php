<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use App\Models\BusinessProfileView;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessProfileView>
 */
class BusinessProfileViewFactory extends Factory
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
            'visitor_key' => hash('sha256', (string) Str::uuid()),
            'viewed_at' => now(),
        ];
    }
}
