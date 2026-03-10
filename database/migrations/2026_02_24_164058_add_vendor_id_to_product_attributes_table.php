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
        Schema::table('product_attributes', function (Blueprint $table) {
            if (!Schema::hasColumn('product_attributes', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->onDelete('cascade');
                $table->index('vendor_id');
            }
        });
        
        // Update the unique constraint on slug to be unique per vendor
        Schema::table('product_attributes', function (Blueprint $table) {
            // Drop the existing unique constraint on slug
            $table->dropUnique(['slug']);
        });
        
        Schema::table('product_attributes', function (Blueprint $table) {
            // Add composite unique constraint on vendor_id and slug
            $table->unique(['vendor_id', 'slug'], 'product_attributes_vendor_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('product_attributes_vendor_slug_unique');
        });
        
        Schema::table('product_attributes', function (Blueprint $table) {
            // Restore the original unique constraint on slug
            $table->unique('slug');
        });
        
        Schema::table('product_attributes', function (Blueprint $table) {
            if (Schema::hasColumn('product_attributes', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
        });
    }
};
