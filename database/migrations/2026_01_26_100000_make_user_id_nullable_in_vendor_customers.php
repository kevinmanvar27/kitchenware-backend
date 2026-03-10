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
     * This migration makes user_id nullable in vendor_customers table
     * to allow vendors to create customers without linking to users table.
     */
    public function up(): void
    {
        if (Schema::hasTable('vendor_customers') && Schema::hasColumn('vendor_customers', 'user_id')) {
            // Drop the foreign key first if it exists
            Schema::table('vendor_customers', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = Schema::getForeignKeys('vendor_customers');
                $hasForeignKey = collect($foreignKeys)->contains(function ($fk) {
                    return in_array('user_id', $fk['columns']);
                });
                
                if ($hasForeignKey) {
                    $table->dropForeign(['user_id']);
                }
            });
            
            // Modify the column to be nullable
            DB::statement('ALTER TABLE vendor_customers MODIFY user_id BIGINT UNSIGNED NULL');
            
            // Re-add the foreign key with nullable support
            Schema::table('vendor_customers', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Cannot reverse this safely as it would require setting a default user_id
        // for any records that have NULL user_id
    }
};
