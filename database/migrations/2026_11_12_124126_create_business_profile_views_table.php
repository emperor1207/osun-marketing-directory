<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('visitor_key', 64);
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(
                ['business_profile_id', 'visitor_key'],
                'business_profile_views_profile_visitor_unique'
            );
            $table->index(
                ['business_profile_id', 'viewed_at'],
                'business_profile_views_profile_viewed_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profile_views');
    }
};
