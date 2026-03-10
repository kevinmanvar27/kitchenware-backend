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
        if (!Schema::hasTable('product_variations')) {
            Schema::create('product_variations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('sku')->unique()->nullable();
                $table->decimal('mrp', 10, 2)->nullable(); // Can override parent product price
                $table->decimal('selling_price', 10, 2)->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->boolean('in_stock')->default(true);
                $table->unsignedBigInteger('image_id')->nullable();
                $table->json('attribute_values'); // Store combination: {"1": 5, "2": 8} (attribute_id: value_id)
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                
                $table->foreign('image_id')->references('id')->on('media')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
