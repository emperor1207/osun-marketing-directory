<?php

namespace Database\Seeders;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Review;
use App\Models\Town;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample businesses, products, services and reviews for demonstrations.
 *
 * Run it on purpose (it is NOT part of DatabaseSeeder):
 *     php artisan db:seed --class=DemoDataSeeder
 *
 * All business names, phone numbers and e-mail addresses are fictional.
 * It can be run again safely: records are updated, not duplicated.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()
            && $this->command !== null
            && ! $this->command->confirm('This is a production environment. Add demo businesses and demo accounts?', false)) {
            return;
        }

        // A new random password each run, so no well-known demo password exists.
        $password = Str::password(12);

        $customers = [];
        foreach ([1 => 'Tolu Adebayo', 2 => 'Funke Ogunleye', 3 => 'Ibrahim Salami'] as $number => $name) {
            $customers[$number] = $this->upsertUser(
                "customer{$number}@demo.osunmarketing.test",
                "demo.customer{$number}",
                $name,
                'customer',
                $password
            );
        }

        $created = 0;

        foreach ($this->businesses() as $index => $data) {
            $category = Category::query()->where('name', $data['category'])->first();
            $town = Town::query()->where('name', $data['town'])->orderBy('id')->first();

            if ($category === null || $town === null) {
                $this->command?->warn("Skipped {$data['name']}: run DatabaseSeeder first (missing category or town).");

                continue;
            }

            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $owner = $this->upsertUser(
                "owner{$number}@demo.osunmarketing.test",
                "demo.owner{$number}",
                $data['owner'],
                'business_owner',
                $password
            );

            $status = $data['status'] ?? 'approved';

            $profile = BusinessProfile::query()->updateOrCreate(
                ['user_id' => $owner->id],
                [
                    'category_id' => $category->id,
                    'town_id' => $town->id,
                    'business_name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'street_address' => $data['address'],
                    'phone' => $data['phone'],
                    'whatsapp' => $data['whatsapp'] ?? $data['phone'],
                    'email' => "owner{$number}@demo.osunmarketing.test",
                    'latitude' => $this->coordinates($data['town'])[0] + ($index * 0.0013),
                    'longitude' => $this->coordinates($data['town'])[1] + ($index * 0.0011),
                    'status' => $status,
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                    'approved_at' => $status === 'approved' ? now() : null,
                ]
            );

            foreach ($data['items'] ?? [] as [$itemName, $itemDescription, $price, $type]) {
                $profile->products()->updateOrCreate(
                    ['name' => $itemName],
                    [
                        'description' => $itemDescription,
                        'price' => $price,
                        'item_type' => $type,
                        'is_available' => true,
                    ]
                );
            }

            foreach ($data['reviews'] ?? [] as [$customerNumber, $rating, $content]) {
                Review::query()->updateOrCreate(
                    ['business_profile_id' => $profile->id, 'customer_id' => $customers[$customerNumber]->id],
                    ['rating' => $rating, 'content' => $content, 'status' => 'visible']
                );
            }

            $created++;
        }

        $this->command?->info("Demo data ready: {$created} businesses.");
        $this->command?->table(['Role', 'Sign in with', 'Password'], [
            ['Customer', 'customer1@demo.osunmarketing.test', $password],
            ['Business owner', 'owner01@demo.osunmarketing.test', $password],
        ]);
        $this->command?->line('Owners are owner01 to owner18 and customers are customer1 to customer3, with the same password. Create the administrator with: php artisan <your promote-administrator command>.');
    }

    private function upsertUser(string $email, string $username, string $name, string $role, string $password): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        // role and email_verified_at are not mass-assignable, so forceFill is used.
        $user->forceFill([
            'name' => $name,
            'username' => $username,
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => $password,
        ])->save();

        return $user;
    }

    /** @return array{float, float} approximate latitude and longitude of the town */
    private function coordinates(string $town): array
    {
        return [
            'Osogbo' => [7.7719, 4.5560],
            'Ede' => [7.7333, 4.4333],
            'Ile-Ife' => [7.4905, 4.5604],
            'Ilesa' => [7.6167, 4.7333],
            'Iwo' => [7.6333, 4.1833],
            'Ikirun' => [7.9167, 4.6667],
            'Ila-Orangun' => [8.0167, 4.9000],
            'Ejigbo' => [7.9000, 4.3167],
            'Ikire' => [7.3667, 4.1833],
            'Ilobu' => [7.8167, 4.4833],
            'Gbongan' => [7.4667, 4.3333],
        ][$town] ?? [7.7719, 4.5560];
    }

    /** @return list<array<string, mixed>> */
    private function businesses(): array
    {
        return [
            [
                'name' => "Adunni's Kitchen & Catering", 'owner' => 'Adunni Bello', 'category' => 'Food and Restaurants', 'town' => 'Osogbo',
                'description' => 'Home-style Nigerian meals, party trays and event catering for offices, weddings and naming ceremonies.',
                'address' => '12 Gbongan Road, Osogbo', 'phone' => '08000000001',
                'items' => [
                    ['Jollof rice and chicken', 'Single plate with fried chicken and plantain.', 2500, 'product'],
                    ['Party jollof tray', 'Serves about 15 people.', 18000, 'product'],
                    ['Small chops platter', 'Spring rolls, samosa, puff-puff and chicken wings.', 6000, 'product'],
                    ['Event catering', 'Full catering service with serving staff for events.', 150000, 'service'],
                ],
                'reviews' => [[1, 5, 'The party jollof was a hit at my sister’s wedding. Delivered on time.'], [2, 4, 'Tasty food and fair prices.']],
            ],
            [
                'name' => 'Ede Fresh Bakery', 'owner' => 'Kamoru Ajayi', 'category' => 'Food and Restaurants', 'town' => 'Ede',
                'description' => 'Fresh bread, pies and celebration cakes baked every morning.',
                'address' => '5 Station Road, Ede', 'phone' => '08000000002',
                'items' => [
                    ['Sliced bread', 'Large family loaf, baked daily.', 1200, 'product'],
                    ['Meat pie', 'Flaky pastry with minced beef filling.', 500, 'product'],
                    ['Birthday cake', 'Vanilla or chocolate cake, 8 inches.', 15000, 'product'],
                    ['Custom cake order', 'Design and bake a cake to your specification.', 25000, 'service'],
                ],
                'reviews' => [[1, 5, 'Best bread in Ede. Always fresh.']],
            ],
            [
                'name' => 'Glow Touch Salon', 'owner' => 'Bukola Ibitoye', 'category' => 'Beauty and Personal Care', 'town' => 'Ile-Ife',
                'description' => 'Hair braiding, wig installation and treatment by trained stylists. Home service available.',
                'address' => '8 Lagere Road, Ile-Ife', 'phone' => '08000000003',
                'items' => [
                    ['Hair braiding', 'Knotless, cornrow and box braids.', 8000, 'service'],
                    ['Wig installation', 'Wig fitting, styling and installation.', 10000, 'service'],
                    ['Hair treatment', 'Deep conditioning and scalp treatment.', 5000, 'service'],
                    ['Natural hair oil', 'Handmade oil blend for growth and shine.', 3500, 'product'],
                ],
                'reviews' => [[1, 5, 'My braids lasted six weeks and looked neat the whole time.'], [2, 5, 'Professional and punctual.'], [3, 4, 'Good work, but book early on weekends.']],
            ],
            [
                'name' => 'Ilesa Stitches Tailoring', 'owner' => 'Segun Fadeyi', 'category' => 'Fashion and Clothing', 'town' => 'Ilesa',
                'description' => 'Native wear, corporate outfits and school uniforms sewn to your measurements.',
                'address' => '21 Ereja Square, Ilesa', 'phone' => '08000000004',
                'items' => [
                    ['Ankara native wear', 'Custom men’s and women’s native outfits.', 15000, 'service'],
                    ['School uniform sewing', 'Per set, bulk discount available.', 6000, 'service'],
                    ['Aso-oke cap', 'Hand-woven cap in assorted colours.', 7500, 'product'],
                ],
                'reviews' => [[2, 4, 'Neat finishing. Delivered before the event.']],
            ],
            [
                'name' => 'Iwo Tech Repair Hub', 'owner' => 'Wasiu Olanrewaju', 'category' => 'Electronics and Information Technology', 'town' => 'Iwo',
                'description' => 'Phone and laptop repairs, accessories and software installation with a 30-day repair warranty.',
                'address' => '3 Oja Oba Road, Iwo', 'phone' => '08000000005',
                'items' => [
                    ['Phone screen replacement', 'Most Android and iPhone models.', 12000, 'service'],
                    ['Laptop repair', 'Diagnosis, repair and cleaning.', 8000, 'service'],
                    ['Phone charger', 'Fast-charging adapter and cable.', 3000, 'product'],
                    ['Power bank 10000mAh', 'Dual-port power bank.', 9500, 'product'],
                ],
                'reviews' => [[3, 5, 'Fixed my cracked screen in one hour.'], [1, 4, 'Fair price and honest advice.']],
            ],
            [
                'name' => 'Ikirun Green Farms', 'owner' => 'Mojisola Alade', 'category' => 'Agriculture and Farming', 'town' => 'Ikirun',
                'description' => 'Farm-fresh eggs, catfish and poultry feed supplied to homes, restaurants and farmers.',
                'address' => 'Km 4, Iwo Road, Ikirun', 'phone' => '08000000006',
                'items' => [
                    ['Fresh eggs (crate)', 'Crate of 30 farm eggs.', 3800, 'product'],
                    ['Catfish (per kg)', 'Live or smoked catfish.', 3000, 'product'],
                    ['Poultry feed (25kg)', 'Layer and grower feed.', 14500, 'product'],
                    ['Farm consultancy', 'Advice on starting a poultry or fish farm.', 20000, 'service'],
                ],
            ],
            [
                'name' => 'Orangun Build & Plumb', 'owner' => 'Taiwo Akinola', 'category' => 'Construction and Home Improvement', 'town' => 'Ila-Orangun',
                'description' => 'Plumbing, electrical wiring and roofing for homes and small commercial buildings.',
                'address' => '14 Market Road, Ila-Orangun', 'phone' => '08000000007',
                'items' => [
                    ['Plumbing repairs', 'Leaks, pipe fitting and borehole connections.', 10000, 'service'],
                    ['Electrical wiring', 'Wiring for rooms and full buildings, priced per room.', 25000, 'service'],
                    ['Roofing sheets', 'Long-span aluminium sheets, per bundle.', 6500, 'product'],
                ],
                'reviews' => [[2, 4, 'Arrived on time and cleaned up after the job.']],
            ],
            [
                'name' => 'Ejigbo Auto Care', 'owner' => 'Lateef Oyewole', 'category' => 'Automotive and Transportation', 'town' => 'Ejigbo',
                'description' => 'Car servicing, oil changes and batteries. Experienced mechanics and genuine parts.',
                'address' => '2 Osogbo Road, Ejigbo', 'phone' => '08000000008',
                'items' => [
                    ['Car servicing', 'Full service with inspection report.', 15000, 'service'],
                    ['Engine oil change', 'Oil and filter replacement.', 7000, 'service'],
                    ['Car battery', '12V maintenance-free battery with 6-month warranty.', 55000, 'product'],
                ],
            ],
            [
                'name' => 'BrightPath Tutorial Centre', 'owner' => 'Grace Oladipo', 'category' => 'Education and Training', 'town' => 'Ikire',
                'description' => 'Exam preparation and computer skills training for students and young adults.',
                'address' => '9 School Road, Ikire', 'phone' => '08000000009',
                'items' => [
                    ['JAMB and WAEC coaching', 'Three-month preparation programme.', 25000, 'service'],
                    ['Computer training', 'Microsoft Office, typing and internet skills.', 30000, 'service'],
                    ['Past question books', 'Latest JAMB and WAEC practice books.', 4000, 'product'],
                ],
                'reviews' => [[3, 5, 'My brother passed his JAMB after joining the classes.']],
            ],
            [
                'name' => 'CareFirst Pharmacy', 'owner' => 'Pharm. Rukayat Yusuf', 'category' => 'Health and Wellness', 'town' => 'Osogbo',
                'description' => 'Licensed community pharmacy offering over-the-counter medicines, supplements and basic health checks.',
                'address' => '27 Ola-Iya Junction, Osogbo', 'phone' => '08000000010',
                'items' => [
                    ['Vitamin C tablets', 'Bottle of 100 tablets.', 1500, 'product'],
                    ['Blood pressure check', 'Quick check with advice.', 500, 'service'],
                    ['First aid kit', 'Home and office first aid kit.', 6500, 'product'],
                ],
                'reviews' => [[1, 5, 'Helpful staff and always well stocked.'], [3, 4, 'Convenient location.']],
            ],
            [
                'name' => 'Osun Glam Events', 'owner' => 'Dayo Famuyiwa', 'category' => 'Events and Entertainment', 'town' => 'Osogbo',
                'description' => 'Event decoration, canopy and chair rentals and photography for weddings and parties.',
                'address' => '6 Oke-Fia Road, Osogbo', 'phone' => '08000000011',
                'items' => [
                    ['Event decoration', 'Hall and outdoor decoration packages.', 80000, 'service'],
                    ['Canopy and chairs rental', 'Per 100 guests.', 30000, 'service'],
                    ['Event photography', 'Full-day coverage with edited photos.', 60000, 'service'],
                ],
                'reviews' => [[2, 5, 'The decoration transformed the hall. Highly recommended.']],
            ],
            [
                'name' => 'Ife Heritage Guest House', 'owner' => 'Mrs. Folake Adeyemi', 'category' => 'Hospitality and Accommodation', 'town' => 'Ile-Ife',
                'description' => 'Clean, quiet guest house near the university with 24-hour power and secure parking.',
                'address' => '15 Ibadan Road, Ile-Ife', 'phone' => '08000000012',
                'items' => [
                    ['Standard room (per night)', 'Air-conditioned room with Wi-Fi.', 15000, 'service'],
                    ['Executive room (per night)', 'Larger room with a work desk.', 25000, 'service'],
                    ['Event hall hire', 'Hall for meetings and small events.', 50000, 'service'],
                ],
                'reviews' => [[3, 5, 'Quiet, clean and good value.']],
            ],
            [
                'name' => 'Ede Mega Provisions', 'owner' => 'Alhaji Musa Raji', 'category' => 'Retail and General Shopping', 'town' => 'Ede',
                'description' => 'Wholesale and retail provisions, rice, oil and household goods at fair prices.',
                'address' => '40 Oja Market, Ede', 'phone' => '08000000013',
                'items' => [
                    ['Rice (50kg bag)', 'Local parboiled rice.', 78000, 'product'],
                    ['Vegetable oil (5 litres)', 'Refined vegetable oil.', 12000, 'product'],
                    ['Bulk delivery', 'Delivery of bulk orders within Ede.', 3000, 'service'],
                ],
            ],
            [
                'name' => 'Gbongan Homes & Land', 'owner' => 'Femi Ogundele', 'category' => 'Real Estate and Property', 'town' => 'Gbongan',
                'description' => 'Land sales, property listing and property management with proper documentation.',
                'address' => '1 Ibadan Road, Gbongan', 'phone' => '08000000014',
                'items' => [
                    ['Residential plot', 'Plot of land with survey plan.', 1500000, 'product'],
                    ['Property management', 'Monthly management of rental properties.', 50000, 'service'],
                ],
            ],
            [
                'name' => 'Ilobu Craft Studio', 'owner' => 'Yetunde Olaniyan', 'category' => 'Arts, Crafts and Creative Services', 'town' => 'Ilobu',
                'description' => 'Adire fabric, logo design and wedding photography by local creatives.',
                'address' => '7 Palace Road, Ilobu', 'phone' => '08000000015',
                'items' => [
                    ['Adire fabric', 'Hand-dyed adire, per 6 yards.', 9000, 'product'],
                    ['Logo design', 'Logo with three revisions.', 15000, 'service'],
                    ['Wedding photography', 'Full-day coverage.', 70000, 'service'],
                ],
                'reviews' => [[1, 5, 'Beautiful adire and quick delivery.']],
            ],

            // Waiting for administrator review: lets you demonstrate the approval queue.
            [
                'name' => 'Iwo Fabric Palace', 'owner' => 'Hajia Sidikat Lawal', 'category' => 'Fashion and Clothing', 'town' => 'Iwo', 'status' => 'pending',
                'description' => 'Lace, Ankara and Aso-oke fabrics sold by the yard.',
                'address' => '18 Central Mosque Road, Iwo', 'phone' => '08000000016',
                'items' => [['Ankara fabric (6 yards)', 'Assorted prints.', 8000, 'product']],
            ],
            [
                'name' => 'Quick Errands Ilesa', 'owner' => 'Seyi Akintunde', 'category' => 'Other Services', 'town' => 'Ilesa', 'status' => 'pending',
                'description' => 'Delivery, shopping and queue-waiting errands within Ilesa.',
                'address' => '4 Isokun Street, Ilesa', 'phone' => '08000000017',
                'items' => [['Local errand', 'Within Ilesa town.', 2000, 'service']],
            ],

            // Rejected: lets you demonstrate the rejection reason shown to the owner.
            [
                'name' => 'Sample Trading Co', 'owner' => 'Test Owner', 'category' => 'Other Services', 'town' => 'Osogbo', 'status' => 'rejected',
                'description' => 'Trading.',
                'address' => 'Osogbo', 'phone' => '08000000018',
                'rejection_reason' => 'Please add a clearer description of what you sell and a working phone number, then submit again.',
            ],
        ];
    }
}
