<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('sender_phone')->nullable();
            $table->text('message');
            $table->string('status')->default('unread'); // unread, read, replied
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_inquiries');
    }
};
