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
        Schema::table('wishlists', function (Blueprint $table) {
            // Add vendor_customer_id to support wishlists for vendor customers (if not exists)
            if (!Schema::hasColumn('wishlists', 'vendor_customer_id')) {
                $table->foreignId('vendor_customer_id')->nullable()->after('user_id')->constrained('vendor_customers')->onDelete('cascade');
            }
            
            // Check if user_id is already nullable
            $columns = DB::select("SHOW COLUMNS FROM wishlists WHERE Field = 'user_id'");
            if (!empty($columns) && $columns[0]->Null === 'NO') {
                // Make user_id nullable since we'll have either user_id or vendor_customer_id
                $table->foreignId('user_id')->nullable()->change();
            }
        });
        
        // Drop the old unique constraint if it exists
        $oldIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = 'wishlists' 
            AND index_name = 'wishlists_user_id_product_id_unique'
        ");

        // Only drop if it's the old constraint (not the new one we want to keep)
        if ($oldIndexExists[0]->count > 0) {
            // Check if vendor_customer_id index also exists - if so, this is the new setup
            $newIndexExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name = 'wishlists' 
                AND index_name = 'wishlists_vendor_customer_id_product_id_unique'
            ");
            
            // If both exist, we're good - this is the new setup
            // If only user_id index exists, we need to keep it (it will be handled by the next migration)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            // Check if vendor_customer_id exists before trying to drop it
            if (Schema::hasColumn('wishlists', 'vendor_customer_id')) {
                // Drop foreign key first
                $table->dropForeign(['vendor_customer_id']);
                $table->dropColumn('vendor_customer_id');
            }
            
            // Make user_id non-nullable again if it's currently nullable
            $columns = DB::select("SHOW COLUMNS FROM wishlists WHERE Field = 'user_id'");
            if (!empty($columns) && $columns[0]->Null === 'YES') {
                $table->foreignId('user_id')->nullable(false)->change();
            }
        });
        
        // Restore the original unique constraint if it doesn't exist
        $indexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE() 
            AND table_name = 'wishlists' 
            AND index_name = 'wishlists_user_id_product_id_unique'
        ");

        if ($indexExists[0]->count == 0) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id']);
            });
        }
    }
};
