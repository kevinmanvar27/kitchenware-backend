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
        Schema::table('product_variations', function (Blueprint $table) {
            // Add low_quantity_threshold column after stock_quantity
            if (!Schema::hasColumn('product_variations', 'low_quantity_threshold')) {
                $table->integer('low_quantity_threshold')->nullable()->default(10)->after('stock_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            if (Schema::hasColumn('product_variations', 'low_quantity_threshold')) {
                $table->dropColumn('low_quantity_threshold');
            }
        });
    }
};
