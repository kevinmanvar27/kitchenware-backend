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
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Add vendor_customer_id column (nullable to support both regular users and vendor customers)
            if (!Schema::hasColumn('shopping_cart_items', 'vendor_customer_id')) {
                $table->unsignedBigInteger('vendor_customer_id')->nullable()->after('user_id');
                
                // Add foreign key constraint
                $table->foreign('vendor_customer_id')
                    ->references('id')
                    ->on('vendor_customers')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('shopping_cart_items', 'vendor_customer_id')) {
                $table->dropForeign(['vendor_customer_id']);
                $table->dropColumn('vendor_customer_id');
            }
        });
    }
};
