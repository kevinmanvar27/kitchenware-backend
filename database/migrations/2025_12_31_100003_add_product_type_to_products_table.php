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
            if (!Schema::hasColumn('products', 'product_type')) {
                $table->enum('product_type', ['simple', 'variable'])->default('simple')->after('name');
            }
            if (!Schema::hasColumn('products', 'product_attributes')) {
                $table->json('product_attributes')->nullable()->after('product_categories'); // Store which attributes this product uses
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('products', 'product_type')) {
                $columns[] = 'product_type';
            }
            if (Schema::hasColumn('products', 'product_attributes')) {
                $columns[] = 'product_attributes';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
