<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('towns', function (Blueprint $table) {
            $table->dropUnique('towns_name_local_government_area_unique');
            $table->dropColumn('local_government_area');
        });
    }

    public function down(): void
    {
        Schema::table('towns', function (Blueprint $table) {
            $table->string('local_government_area');
            $table->unique(['name', 'local_government_area']);
        });
    }
};
