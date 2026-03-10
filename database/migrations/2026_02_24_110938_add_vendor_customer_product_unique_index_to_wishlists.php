<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if vendor_customer_id column exists before adding index
        if (Schema::hasColumn('wishlists', 'vendor_customer_id')) {
            // Check if the unique index for vendor_customer doesn't already exist
            $indexExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name = 'wishlists' 
                AND index_name = 'wishlists_vendor_customer_id_product_id_unique'
            ");

            if ($indexExists[0]->count == 0) {
                Schema::table('wishlists', function (Blueprint $table) {
                    // Add unique constraint for vendor_customer_id and product_id combination
                    // This ensures a vendor customer can't add the same product to wishlist twice
                    $table->unique(['vendor_customer_id', 'product_id'], 'wishlists_vendor_customer_id_product_id_unique');
                });
            }
        }
        
        // Check if user_id column exists before adding index
        if (Schema::hasColumn('wishlists', 'user_id')) {
            // Check if the unique index for user doesn't already exist
            $userIndexExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name = 'wishlists' 
                AND index_name = 'wishlists_user_id_product_id_unique'
            ");

            if ($userIndexExists[0]->count == 0) {
                Schema::table('wishlists', function (Blueprint $table) {
                    // Add unique constraint for user_id and product_id combination
                    // This ensures a regular user can't add the same product to wishlist twice
                    $table->unique(['user_id', 'product_id'], 'wishlists_user_id_product_id_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check and drop indexes only if they exist
        $vendorIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = 'wishlists' 
            AND index_name = 'wishlists_vendor_customer_id_product_id_unique'
        ");

        if ($vendorIndexExists[0]->count > 0) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropUnique('wishlists_vendor_customer_id_product_id_unique');
            });
        }
        
        $userIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = 'wishlists' 
            AND index_name = 'wishlists_user_id_product_id_unique'
        ");

        if ($userIndexExists[0]->count > 0) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropUnique('wishlists_user_id_product_id_unique');
            });
        }
    }
};
