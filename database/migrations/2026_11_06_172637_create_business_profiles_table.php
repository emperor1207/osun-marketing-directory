<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();

            // Business owner
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            // Business classification and location
            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('town_id')
                ->constrained('towns')
                ->restrictOnDelete();

            // Business information
            $table->string('business_name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('street_address')->nullable();

            // Contact information
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Business images
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();

            // Map coordinates
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Administrator approval
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['town_id', 'slug']);
            $table->index('status');
            $table->index('category_id');
            $table->index('town_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
