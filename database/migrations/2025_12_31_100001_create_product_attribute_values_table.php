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
        if (!Schema::hasTable('product_attribute_values')) {
            Schema::create('product_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
                $table->string('value'); // e.g., "Small", "Red", "Cotton"
                $table->string('slug');
                $table->string('color_code')->nullable(); // For color attributes
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->unique(['attribute_id', 'slug']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
