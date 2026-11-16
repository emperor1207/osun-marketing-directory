<?php

namespace Database\Seeders;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Review;
use App\Models\Town;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fictional businesses with logos, cover pictures, listings and photo galleries,
 * for demonstrations. The data is in database/seeders/demo-assets/businesses.json
 * and the generated artwork sits next to it.
 *
 * Run it on purpose (it is NOT part of DatabaseSeeder):
 *     php artisan db:seed --class=DemoDataSeeder
 *
 * All business names, phone numbers, e-mail addresses and pictures are fictional.
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

        $businesses = json_decode(
            (string) file_get_contents(database_path('seeders/demo-assets/businesses.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $created = 0;

        foreach ($businesses as $index => $data) {
            $key = $data['key'];
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

            $status = $data['status'];
            [$latitude, $longitude] = $this->coordinates($data['town']);

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
                    'whatsapp' => $data['whatsapp'],
                    'email' => "owner{$number}@demo.osunmarketing.test",
                    'logo' => $this->publish("logos/{$key}.png", "business-profiles/logo/demo-{$key}.png"),
                    'cover_image' => $this->publish("covers/{$key}.jpg", "business-profiles/cover_image/demo-{$key}.jpg"),
                    'latitude' => $latitude + ($index * 0.0013),
                    'longitude' => $longitude + ($index * 0.0011),
                    'status' => $status,
                    'rejection_reason' => $data['rejection_reason'],
                    'approved_at' => $status === 'approved' ? now() : null,
                ]
            );

            foreach ($data['items'] as $itemIndex => [$itemName, $itemDescription, $price, $type]) {
                $product = $profile->products()->updateOrCreate(
                    ['name' => $itemName],
                    [
                        'description' => $itemDescription,
                        'price' => $price,
                        'item_type' => $type,
                        'is_available' => true,
                        'image' => $this->publish(
                            "items/{$key}-i{$itemIndex}-0.jpg",
                            "business-items/demo-{$key}-i{$itemIndex}-0.jpg"
                        ),
                    ]
                );

                for ($photo = 1; $photo <= ($data['gallery_counts'][$itemIndex] ?? 0); $photo++) {
                    $path = $this->publish(
                        "items/{$key}-i{$itemIndex}-{$photo}.jpg",
                        "business-items/gallery/demo-{$key}-i{$itemIndex}-{$photo}.jpg"
                    );

                    if ($path !== null) {
                        $product->images()->updateOrCreate(['path' => $path], ['sort_order' => $photo]);
                    }
                }
            }

            foreach ($data['reviews'] as [$customerNumber, $rating, $content]) {
                Review::query()->updateOrCreate(
                    ['business_profile_id' => $profile->id, 'customer_id' => $customers[$customerNumber]->id],
                    ['rating' => $rating, 'content' => $content, 'status' => 'visible']
                );
            }

            $created++;
        }

        $this->command?->info("Demo data ready: {$created} businesses with logos, covers and photo galleries.");
        $this->command?->table(['Role', 'Sign in with', 'Password'], [
            ['Customer', 'customer1@demo.osunmarketing.test', $password],
            ['Business owner', 'owner01@demo.osunmarketing.test', $password],
        ]);
        $this->command?->line('Owners are owner01 to owner30 and customers are customer1 to customer3, with the same password.');
        $this->command?->line('If pictures do not show, run: php artisan storage:link');
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

    /** Copies a demo picture into the media disk and returns its stored path. */
    private function publish(string $asset, string $target): ?string
    {
        $source = database_path("seeders/demo-assets/{$asset}");

        if (! is_file($source)) {
            return null;
        }

        Storage::disk(config('filesystems.media_disk'))->put($target, (string) file_get_contents($source), 'public');

        return $target;
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
}
