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
        // Check if the products table exists
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('mrp', 10, 2);
                $table->decimal('selling_price', 10, 2)->nullable();
                $table->boolean('in_stock')->default(true);
                $table->integer('stock_quantity')->default(0);
                $table->string('status')->default('draft'); // draft, published
                $table->unsignedBigInteger('main_photo_id')->nullable();
                $table->json('product_gallery')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->timestamps();
                
                // Foreign key constraint
                $table->foreign('main_photo_id')->references('id')->on('media')->onDelete('set null');
            });
        } else {
            // Table exists, check and add any missing columns
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'name')) {
                    $table->string('name');
                }
                if (!Schema::hasColumn('products', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('products', 'mrp')) {
                    $table->decimal('mrp', 10, 2);
                }
                if (!Schema::hasColumn('products', 'selling_price')) {
                    $table->decimal('selling_price', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('products', 'in_stock')) {
                    $table->boolean('in_stock')->default(true);
                }
                if (!Schema::hasColumn('products', 'stock_quantity')) {
                    $table->integer('stock_quantity')->default(0);
                }
                if (!Schema::hasColumn('products', 'status')) {
                    $table->string('status')->default('draft');
                }
                if (!Schema::hasColumn('products', 'main_photo_id')) {
                    $table->unsignedBigInteger('main_photo_id')->nullable();
                    $table->foreign('main_photo_id')->references('id')->on('media')->onDelete('set null');
                }
                if (!Schema::hasColumn('products', 'product_gallery')) {
                    $table->json('product_gallery')->nullable();
                }
                if (!Schema::hasColumn('products', 'meta_title')) {
                    $table->string('meta_title')->nullable();
                }
                if (!Schema::hasColumn('products', 'meta_description')) {
                    $table->text('meta_description')->nullable();
                }
                if (!Schema::hasColumn('products', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable();
                }
                if (!Schema::hasColumn('products', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};