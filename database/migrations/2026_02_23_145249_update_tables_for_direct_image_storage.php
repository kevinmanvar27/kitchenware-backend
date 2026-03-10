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
        // Update categories table
        if (Schema::hasColumn('categories', 'image_id')) {
            Schema::table('categories', function (Blueprint $table) {
                // Drop foreign key constraint first if it exists
                try {
                    $table->dropForeign(['image_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('image_id');
            });
        }
        
        if (!Schema::hasColumn('categories', 'image')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('image')->nullable()->after('description');
            });
        }
        
        // Update sub_categories table
        if (Schema::hasColumn('sub_categories', 'image_id')) {
            Schema::table('sub_categories', function (Blueprint $table) {
                // Drop foreign key constraint first if it exists
                try {
                    $table->dropForeign(['image_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('image_id');
            });
        }
        
        if (!Schema::hasColumn('sub_categories', 'image')) {
            Schema::table('sub_categories', function (Blueprint $table) {
                $table->string('image')->nullable()->after('description');
            });
        }
        
        // Update products table
        if (Schema::hasColumn('products', 'main_photo_id')) {
            Schema::table('products', function (Blueprint $table) {
                // Drop foreign key constraint first if it exists
                try {
                    $table->dropForeign(['main_photo_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('main_photo_id');
            });
        }
        
        if (!Schema::hasColumn('products', 'main_photo')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('main_photo')->nullable()->after('status');
            });
        }
        
        // Update product_variations table
        if (Schema::hasColumn('product_variations', 'image_id')) {
            Schema::table('product_variations', function (Blueprint $table) {
                // Drop foreign key constraint first if it exists
                try {
                    $table->dropForeign(['image_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                $table->dropColumn('image_id');
            });
        }
        
        if (!Schema::hasColumn('product_variations', 'image')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->string('image')->nullable()->after('in_stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert categories table
        if (Schema::hasColumn('categories', 'image')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
        
        if (!Schema::hasColumn('categories', 'image_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('image_id')->nullable()->after('description');
            });
        }
        
        // Revert sub_categories table
        if (Schema::hasColumn('sub_categories', 'image')) {
            Schema::table('sub_categories', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
        
        if (!Schema::hasColumn('sub_categories', 'image_id')) {
            Schema::table('sub_categories', function (Blueprint $table) {
                $table->unsignedBigInteger('image_id')->nullable()->after('description');
            });
        }
        
        // Revert products table
        if (Schema::hasColumn('products', 'main_photo')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('main_photo');
            });
        }
        
        if (!Schema::hasColumn('products', 'main_photo_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('main_photo_id')->nullable()->after('status');
            });
        }
        
        // Revert product_variations table
        if (Schema::hasColumn('product_variations', 'image')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
        
        if (!Schema::hasColumn('product_variations', 'image_id')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->unsignedBigInteger('image_id')->nullable()->after('in_stock');
            });
        }
    }
};
