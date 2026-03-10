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
        Schema::create('referral_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('referred_vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('user_subscriptions')->onDelete('cascade');
            $table->string('referral_code');
            $table->decimal('subscription_amount', 10, 2);
            $table->decimal('commission_percentage', 5, 2)->default(10.00);
            $table->decimal('commission_amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('payout_id')->nullable()->constrained('vendor_payouts')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('referrer_vendor_id');
            $table->index('referred_vendor_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_earnings');
    }
};
