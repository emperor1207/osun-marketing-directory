<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('item_type', 20);
            $table->boolean('is_available')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();

            $table->index(
                ['business_profile_id', 'item_type', 'is_available'],
                'products_profile_type_available_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
