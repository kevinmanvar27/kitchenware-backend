<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class TestProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if test product already exists
        if (Product::where('slug', 'test-product-with-image')->exists()) {
            return;
        }
        
        // Create a test product with direct image storage
        Product::create([
            'name' => 'Test Product with Image',
            'slug' => Str::slug('Test Product with Image'),
            'description' => 'This is a test product to verify image display',
            'mrp' => 100.00,
            'selling_price' => 80.00,
            'in_stock' => true,
            'stock_quantity' => 10,
            'status' => 'published',
            'main_photo' => null, // Direct image storage - can be updated later
            'product_gallery' => [],
        ]);
    }
}