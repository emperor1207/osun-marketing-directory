<?php

namespace App\Console\Commands;

use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Town;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('app:import-businesses {file : Path to the CSV file} {--approve : Publish the businesses straight away instead of leaving them pending} {--dry-run : Check the file and report what would happen without saving anything}')]
#[Description('Create owner accounts, business profiles and listings from a CSV file')]
class ImportBusinesses extends Command
{
    public function handle(): int
    {
        $path = (string) $this->argument('file');

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("Cannot read the file: {$path}");

            return self::FAILURE;
        }

        $rows = $this->readCsv($path);

        if ($rows === []) {
            $this->error('The file has no business rows. Check that the first line is the header row.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $approve = (bool) $this->option('approve');
        $report = [];
        $credentials = [];
        $counts = ['created' => 0, 'skipped' => 0, 'invalid' => 0];

        foreach ($rows as [$line, $row]) {
            [$outcome, $message, $credential] = $this->importRow($row, $dryRun, $approve);

            $counts[$outcome]++;
            $report[] = [$line, $row['business_name'] ?? '', $outcome, $message];

            if ($credential !== null) {
                $credentials[] = $credential;
            }
        }

        $this->table(['Line', 'Business', 'Result', 'Note'], $report);
        $verb = $dryRun ? 'Would create' : 'Created';
        $this->info("{$verb}: {$counts['created']} | Skipped: {$counts['skipped']} | Invalid: {$counts['invalid']}");

        if ($dryRun) {
            $this->comment('Dry run: nothing was saved.');

            return self::SUCCESS;
        }

        if ($credentials !== []) {
            $file = 'imports/credentials-'.now()->format('Ymd-His').'.csv';
            Storage::disk('local')->put($file, $this->credentialsCsv($credentials));

            $this->newLine();
            $this->warn('Owner logins were saved to: '.Storage::disk('local')->path($file));
            $this->warn('This file contains passwords. Share each login only with its owner, then delete the file.');
        }

        if (! $approve && $counts['created'] > 0) {
            $this->comment('Businesses are PENDING. Approve them in the admin area once the owners agree to be listed.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $row
     * @return array{0: string, 1: string, 2: array<string, string>|null}
     */
    private function importRow(array $row, bool $dryRun, bool $approve): array
    {
        $name = $row['business_name'] ?? '';

        if ($name === '' || mb_strlen($name) > 255) {
            return ['invalid', 'business_name is required (255 characters at most).', null];
        }

        $category = Category::query()->where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($row['category'] ?? '')])->first();

        if ($category === null) {
            return ['invalid', "Unknown category '".($row['category'] ?? '')."'. Use the exact name from the admin Categories page.", null];
        }

        $town = Town::query()->where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($row['town'] ?? '')])->orderBy('id')->first();

        if ($town === null) {
            return ['invalid', "Unknown town '".($row['town'] ?? '')."'. Use the exact name from the admin Locations page.", null];
        }

        foreach (['phone', 'whatsapp'] as $field) {
            if (mb_strlen($row[$field] ?? '') > 30) {
                return ['invalid', "{$field} is longer than 30 characters.", null];
            }
        }

        $email = $row['owner_email'] ?? '';
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return ['invalid', "owner_email '{$email}' is not a valid email address.", null];
        }

        $website = $row['website'] ?? '';
        if ($website !== '' && (filter_var($website, FILTER_VALIDATE_URL) === false || ! preg_match('#^https?://#i', $website))) {
            return ['invalid', 'website must start with http:// or https://.', null];
        }

        $latitude = $row['latitude'] ?? '';
        $longitude = $row['longitude'] ?? '';
        if (($latitude === '') !== ($longitude === '')
            || ($latitude !== '' && (! is_numeric($latitude) || ! is_numeric($longitude) || abs((float) $latitude) > 90 || abs((float) $longitude) > 180))) {
            return ['invalid', 'latitude and longitude must both be given as valid numbers.', null];
        }

        $items = $this->parseItems($row['items'] ?? '');
        if (is_string($items)) {
            return ['invalid', $items, null];
        }

        $slug = Str::slug($name) ?: 'business';

        if (BusinessProfile::query()->where('town_id', $town->id)->where('slug', $slug)->exists()) {
            return ['skipped', 'A business with this name already exists in this town.', null];
        }

        $loginEmail = $email !== '' ? mb_strtolower($email) : "{$slug}-{$town->slug}@listed.osunmarketing.invalid";

        if (User::query()->where('email', $loginEmail)->exists()) {
            return ['skipped', "An account already uses {$loginEmail}.", null];
        }

        if ($dryRun) {
            return ['created', 'Would be created with '.count($items).' listing(s).', null];
        }

        $password = Str::password(14);

        DB::transaction(function () use ($row, $name, $slug, $category, $town, $loginEmail, $password, $items, $approve, $latitude, $longitude): void {
            $owner = new User;
            $owner->forceFill([
                'name' => ($row['owner_name'] ?? '') !== '' ? $row['owner_name'] : $name,
                'username' => $this->uniqueUsername($slug),
                'email' => $loginEmail,
                'role' => 'business_owner',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $password,
            ])->save();

            $profile = $owner->businessProfile()->create([
                'category_id' => $category->id,
                'town_id' => $town->id,
                'business_name' => $name,
                'slug' => $slug,
                'description' => ($row['description'] ?? '') ?: null,
                'street_address' => ($row['street_address'] ?? '') ?: null,
                'phone' => ($row['phone'] ?? '') ?: null,
                'whatsapp' => ($row['whatsapp'] ?? '') ?: (($row['phone'] ?? '') ?: null),
                'email' => ($row['owner_email'] ?? '') ?: null,
                'website' => ($row['website'] ?? '') ?: null,
                'latitude' => $latitude !== '' ? (float) $latitude : null,
                'longitude' => $longitude !== '' ? (float) $longitude : null,
                'status' => $approve ? 'approved' : 'pending',
                'approved_at' => $approve ? now() : null,
            ]);

            foreach ($items as [$itemName, $type, $price]) {
                $profile->products()->create([
                    'name' => $itemName,
                    'item_type' => $type,
                    'price' => $price,
                    'is_available' => true,
                ]);
            }
        });

        return ['created', ($approve ? 'Approved' : 'Pending').' with '.count($items).' listing(s).', [
            'business' => $name,
            'town' => $town->name,
            'login_email' => $loginEmail,
            'password' => $password,
            'status' => $approve ? 'approved' : 'pending',
        ]];
    }

    /**
     * Items look like: Hair braiding:service:8000; Hair oil:product:3500
     *
     * @return list<array{0: string, 1: string, 2: float}>|string a list, or an error message
     */
    private function parseItems(string $text): array|string
    {
        $items = [];

        foreach (array_filter(array_map('trim', explode(';', $text))) as $entry) {
            $parts = array_map('trim', explode(':', $entry));

            if (count($parts) !== 3 || $parts[0] === '' || mb_strlen($parts[0]) > 255) {
                return "Item '{$entry}' must look like name:type:price.";
            }

            if (! in_array($parts[1], ['product', 'service'], true)) {
                return "Item '{$parts[0]}' needs the type product or service.";
            }

            if (! is_numeric($parts[2]) || (float) $parts[2] < 0) {
                return "Item '{$parts[0]}' needs a price that is a number (use 0 for price on request).";
            }

            $items[] = [$parts[0], $parts[1], (float) $parts[2]];
        }

        return $items;
    }

    private function uniqueUsername(string $slug): string
    {
        $base = Str::limit('owner-'.$slug, 40, '');
        $username = $base;
        $suffix = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.'-'.++$suffix;
        }

        return $username;
    }

    /**
     * @return list<array{0: int, 1: array<string, string>}>
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        $header = null;
        $rows = [];
        $line = 0;

        while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $line++;

            if ($header === null) {
                $values[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $values[0]);
                $header = array_map(fn ($name) => Str::snake(trim((string) $name)), $values);

                continue;
            }

            $values = array_map(fn ($value) => trim((string) $value), array_pad($values, count($header), ''));

            if (implode('', $values) === '') {
                continue;
            }

            $rows[] = [$line, array_combine($header, array_slice($values, 0, count($header)))];
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<array<string, string>>  $credentials
     */
    private function credentialsCsv(array $credentials): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, array_keys($credentials[0]), ',', '"', '');

        foreach ($credentials as $credential) {
            fputcsv($stream, $credential, ',', '"', '');
        }

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return $contents;
    }
}
