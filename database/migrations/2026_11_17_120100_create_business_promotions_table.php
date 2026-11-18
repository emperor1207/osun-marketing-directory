<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('discount_code')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_promotions');
    }
};
