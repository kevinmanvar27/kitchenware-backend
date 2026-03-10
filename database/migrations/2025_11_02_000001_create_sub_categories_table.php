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
        if (!Schema::hasTable('sub_categories')) {
            Schema::create('sub_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('image_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Foreign key constraints
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreign('image_id')->references('id')->on('media')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};