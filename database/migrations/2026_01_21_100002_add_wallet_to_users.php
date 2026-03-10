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
        // Add wallet balance to users for storing referral rewards
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wallet_balance')) {
                $table->decimal('wallet_balance', 10, 2)->default(0)->after('discount_percentage');
            }
        });
        
        // Create wallet transactions table to track all wallet activities
        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['credit', 'debit']);
                $table->decimal('amount', 10, 2);
                $table->decimal('balance_after', 10, 2);
                $table->string('description');
                $table->string('reference_type')->nullable(); // e.g., 'referral', 'order', 'manual'
                $table->unsignedBigInteger('reference_id')->nullable(); // e.g., referral_id, order_id
                $table->timestamps();
                
                $table->index(['user_id', 'type']);
                $table->index(['reference_type', 'reference_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wallet_balance')) {
                $table->dropColumn('wallet_balance');
            }
        });
        
        Schema::dropIfExists('wallet_transactions');
    }
};
