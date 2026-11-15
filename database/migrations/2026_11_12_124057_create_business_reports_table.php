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
        Schema::create('business_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('customer_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('reason', 40);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->text('resolution_note')->nullable();
            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['business_profile_id', 'customer_id'],
                'business_reports_profile_customer_unique'
            );
            $table->index(['status', 'created_at'], 'business_reports_status_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_reports');
    }
};
