<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable();
        });

        DB::table('users')
            ->select(['id', 'email'])
            ->orderBy('id')
            ->chunkById(500, function ($users): void {
                foreach ($users as $user) {
                    $emailPrefix = Str::lower(Str::before($user->email, '@'));

$base = preg_replace('/[^a-z0-9]+/', '-', $emailPrefix) ?? '';

$base = trim(Str::substr($base, 0, 50), '-');
                    $base = Str::length($base) >= 3 ? $base : 'user-'.$user->id;
                    $username = $base;
                    $suffix = '-'.$user->id;
                    $attempt = 1;

                    while (DB::table('users')->whereRaw('LOWER(username) = ?', [Str::lower($username)])->exists()) {
                        $numberedSuffix = $suffix.($attempt > 1 ? '-'.$attempt : '');
                        $username = Str::substr($base, 0, 50 - Str::length($numberedSuffix)).$numberedSuffix;
                        $attempt++;
                    }

                    DB::table('users')->where('id', $user->id)->update(['username' => $username]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable(false)->change();
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
