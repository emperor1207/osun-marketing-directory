<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('towns', function (Blueprint $table) {
            $table->dropUnique('towns_slug_unique');

            $table->unique(
                ['local_government_area_id', 'slug'],
                'towns_lga_slug_unique'
            );

            $table->unique(
                ['local_government_area_id', 'name'],
                'towns_lga_name_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('towns', function (Blueprint $table) {
            $table->dropUnique('towns_lga_slug_unique');
            $table->dropUnique('towns_lga_name_unique');

            $table->unique('slug', 'towns_slug_unique');
        });
    }
};
