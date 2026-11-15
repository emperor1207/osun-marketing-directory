<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Agriculture and Farming',
                'description' => 'Farms, agricultural products, livestock and related services.',
            ],
            [
                'name' => 'Automotive and Transportation',
                'description' => 'Vehicle sales, repairs, spare parts and transport services.',
            ],
            [
                'name' => 'Beauty and Personal Care',
                'description' => 'Salons, barbershops, skincare and personal care services.',
            ],
            [
                'name' => 'Construction and Home Improvement',
                'description' => 'Building contractors, plumbing, electrical and renovation services.',
            ],
            [
                'name' => 'Education and Training',
                'description' => 'Schools, tutorial centres, vocational training and educational services.',
            ],
            [
                'name' => 'Electronics and Information Technology',
                'description' => 'Electronics, computers, mobile devices and IT services.',
            ],
            [
                'name' => 'Fashion and Clothing',
                'description' => 'Clothing stores, fashion designers, tailors and accessories.',
            ],
            [
                'name' => 'Food and Restaurants',
                'description' => 'Restaurants, food vendors, catering and food businesses.',
            ],
            [
                'name' => 'Health and Wellness',
                'description' => 'Healthcare providers, pharmacies, fitness and wellness services.',
            ],
            [
                'name' => 'Hospitality and Accommodation',
                'description' => 'Hotels, guest houses and accommodation services.',
            ],
            [
                'name' => 'Professional and Business Services',
                'description' => 'Consultants, accountants, legal support and other professional services.',
            ],
            [
                'name' => 'Real Estate and Property',
                'description' => 'Property sales, rentals, estate agents and property management.',
            ],
            [
                'name' => 'Retail and General Shopping',
                'description' => 'Shops, supermarkets, distributors and general merchandise.',
            ],
            [
                'name' => 'Arts, Crafts and Creative Services',
                'description' => 'Artists, photographers, graphic designers and craft businesses.',
            ],
            [
                'name' => 'Events and Entertainment',
                'description' => 'Event planners, decorators, entertainers and recreational services.',
            ],
            [
                'name' => 'Travel and Tourism',
                'description' => 'Travel agencies, tour operators and tourism-related services.',
            ],
            [
                'name' => 'Other Services',
                'description' => 'Businesses and services that do not fit into the other categories.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
