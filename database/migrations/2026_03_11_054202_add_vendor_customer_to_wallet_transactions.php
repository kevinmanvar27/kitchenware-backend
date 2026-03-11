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
        // Add vendor_customer_id to wallet_transactions if not exists
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'vendor_customer_id')) {
                // Make user_id nullable
                $table->foreignId('user_id')->nullable()->change();
                
                // Add vendor_customer_id
                $table->foreignId('vendor_customer_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
                
                // Add index
                $table->index(['vendor_customer_id', 'type']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'vendor_customer_id')) {
                $table->dropForeign(['vendor_customer_id']);
                $table->dropIndex(['vendor_customer_id', 'type']);
                $table->dropColumn('vendor_customer_id');
                
                // Make user_id not nullable again
                $table->foreignId('user_id')->nullable(false)->change();
            }
        });
    }
};
