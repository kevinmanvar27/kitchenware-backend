<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the unique constraints on shopping_cart_items table
     * to include product_variation_id, allowing users to add different variations
     * of the same product to their cart.
     */
    public function up(): void
    {
        // First, remove any duplicate entries that might exist
        // Keep only the most recent entry for each user/session + product + variation combination
        $this->removeDuplicateCartItems();
        
        // Drop foreign key on user_id if it exists (using raw SQL to check first)
        $this->dropForeignKeyIfExistsRaw('shopping_cart_items', 'shopping_cart_items_user_id_foreign');
        
        // Drop unique indexes using raw SQL
        $this->dropIndexIfExistsRaw('shopping_cart_items', 'shopping_cart_items_user_product_unique');
        $this->dropIndexIfExistsRaw('shopping_cart_items', 'shopping_cart_items_session_product_unique');
        $this->dropIndexIfExistsRaw('shopping_cart_items', 'shopping_cart_items_user_id_product_id_unique');
        $this->dropIndexIfExistsRaw('shopping_cart_items', 'shopping_cart_items_session_id_product_id_unique');
        
        // Add new unique constraints that include product_variation_id
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // This allows different variations of the same product in the cart
            $table->unique(
                ['user_id', 'product_id', 'product_variation_id'], 
                'cart_user_product_variation_unique'
            );
            $table->unique(
                ['session_id', 'product_id', 'product_variation_id'], 
                'cart_session_product_variation_unique'
            );
        });
        
        // Re-add foreign key on user_id
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Remove duplicate cart items before applying new constraints
     */
    private function removeDuplicateCartItems(): void
    {
        try {
            // For authenticated users - keep the most recent entry
            DB::statement("
                DELETE t1 FROM shopping_cart_items t1
                INNER JOIN shopping_cart_items t2
                WHERE t1.id < t2.id
                AND t1.user_id IS NOT NULL
                AND t1.user_id = t2.user_id
                AND t1.product_id = t2.product_id
                AND (t1.product_variation_id = t2.product_variation_id 
                     OR (t1.product_variation_id IS NULL AND t2.product_variation_id IS NULL))
            ");
        } catch (\Exception $e) {
            // Ignore if no duplicates
        }
        
        try {
            // For guest users - keep the most recent entry
            DB::statement("
                DELETE t1 FROM shopping_cart_items t1
                INNER JOIN shopping_cart_items t2
                WHERE t1.id < t2.id
                AND t1.session_id IS NOT NULL
                AND t1.session_id = t2.session_id
                AND t1.product_id = t2.product_id
                AND (t1.product_variation_id = t2.product_variation_id 
                     OR (t1.product_variation_id IS NULL AND t2.product_variation_id IS NULL))
            ");
        } catch (\Exception $e) {
            // Ignore if no duplicates
        }
    }

    /**
     * Helper to drop foreign key if it exists using raw SQL
     */
    private function dropForeignKeyIfExistsRaw(string $tableName, string $foreignKey): void
    {
        $database = config('database.connections.mysql.database');
        $exists = DB::select("
            SELECT COUNT(*) as cnt 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ? 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$database, $tableName, $foreignKey]);
        
        if ($exists[0]->cnt > 0) {
            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$foreignKey}`");
        }
    }

    /**
     * Helper to drop index if it exists using raw SQL
     */
    private function dropIndexIfExistsRaw(string $tableName, string $indexName): void
    {
        $database = config('database.connections.mysql.database');
        $exists = DB::select("
            SELECT COUNT(*) as cnt 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$database, $tableName, $indexName]);
        
        if ($exists[0]->cnt > 0) {
            DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`");
        }
    }

    /**
     * Helper to drop index if it exists (for Schema builder)
     */
    private function dropIndexIfExists($table, $indexName): void
    {
        try {
            $table->dropUnique($indexName);
        } catch (\Exception $e) {
            // Index doesn't exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key first
        $this->dropForeignKeyIfExistsRaw('shopping_cart_items', 'shopping_cart_items_user_id_foreign');
        
        // Drop the new unique constraints
        $this->dropIndexIfExistsRaw('shopping_cart_items', 'cart_user_product_variation_unique');
        $this->dropIndexIfExistsRaw('shopping_cart_items', 'cart_session_product_variation_unique');
        
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Restore the original unique constraints (without variation)
            $table->unique(['user_id', 'product_id'], 'shopping_cart_items_user_product_unique');
            $table->unique(['session_id', 'product_id'], 'shopping_cart_items_session_product_unique');
            
            // Re-add foreign key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
