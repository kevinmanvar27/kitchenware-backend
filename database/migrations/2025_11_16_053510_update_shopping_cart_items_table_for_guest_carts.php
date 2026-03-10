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
        // Check if session_id column already exists
        if (Schema::hasColumn('shopping_cart_items', 'session_id')) {
            return; // Already migrated
        }
        
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Drop foreign key constraints first
            $this->dropForeignKeyIfExists($table, 'shopping_cart_items_user_id_foreign');
            $this->dropForeignKeyIfExists($table, 'shopping_cart_items_product_id_foreign');
            
            // Drop the existing unique constraint if exists
            $this->dropUniqueIfExists('shopping_cart_items', ['user_id', 'product_id']);
        });
        
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Make user_id nullable to support guest carts
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add session_id for guest users
            $table->string('session_id')->nullable()->after('user_id');
            
            // Add new unique constraints for both authenticated and guest users
            $table->unique(['user_id', 'product_id'], 'shopping_cart_items_user_product_unique');
            $table->unique(['session_id', 'product_id'], 'shopping_cart_items_session_product_unique');
            
            // Re-add foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
    
    /**
     * Helper to drop foreign key if it exists
     */
    private function dropForeignKeyIfExists($table, $foreignKey)
    {
        try {
            $table->dropForeign($foreignKey);
        } catch (\Exception $e) {
            // Foreign key doesn't exist, ignore
        }
    }
    
    /**
     * Helper to drop unique constraint if it exists
     */
    private function dropUniqueIfExists($tableName, $columns)
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->dropUnique($columns);
            });
        } catch (\Exception $e) {
            // Unique constraint doesn't exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('shopping_cart_items', 'session_id')) {
            return; // Not migrated
        }
        
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Drop foreign key constraints first
            $this->dropForeignKeyIfExists($table, 'shopping_cart_items_user_id_foreign');
            $this->dropForeignKeyIfExists($table, 'shopping_cart_items_product_id_foreign');
            
            // Drop the new unique constraints
            try {
                $table->dropUnique('shopping_cart_items_user_product_unique');
            } catch (\Exception $e) {}
            
            try {
                $table->dropUnique('shopping_cart_items_session_product_unique');
            } catch (\Exception $e) {}
        });
        
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Restore the original unique constraint
            $table->unique(['user_id', 'product_id']);
            
            // Remove session_id column
            $table->dropColumn('session_id');
            
            // Make user_id not nullable again
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            // Re-add foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};