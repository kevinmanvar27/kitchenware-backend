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
        Schema::table('products', function (Blueprint $table) {
            // Add JSON column to store selected categories and subcategories
            if (!Schema::hasColumn('products', 'product_categories')) {
                $table->json('product_categories')->nullable()->after('product_gallery');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'product_categories')) {
                $table->dropColumn('product_categories');
            }
        });
    }
};