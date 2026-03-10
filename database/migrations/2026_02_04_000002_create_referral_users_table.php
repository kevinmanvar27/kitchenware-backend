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
        if (!Schema::hasTable('referral_users')) {
            Schema::create('referral_users', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referral_id')->constrained('referrals')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone_number', 20)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index('referral_id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_users');
    }
};
