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
        // Add wallet balance to vendor_customers
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_customers', 'wallet_balance')) {
                $table->decimal('wallet_balance', 10, 2)->default(0)->after('discount_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_customers', 'wallet_balance')) {
                $table->dropColumn('wallet_balance');
            }
        });
    }
};
