<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('towns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('local_government_area');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'local_government_area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('towns');
    }
};
