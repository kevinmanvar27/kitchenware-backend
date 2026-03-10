<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
            
            // Update existing products with slugs
            DB::table('products')->whereNull('slug')->orWhere('slug', '')->orderBy('id')->chunk(100, function ($products) {
                foreach ($products as $product) {
                    $slug = Str::slug($product->name);
                    // Ensure uniqueness
                    $originalSlug = $slug;
                    $count = 1;
                    while (DB::table('products')->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                        $slug = $originalSlug . '-' . $count;
                        $count++;
                    }
                    DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
                }
            });
            
            // Now make the slug column unique and not nullable
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};