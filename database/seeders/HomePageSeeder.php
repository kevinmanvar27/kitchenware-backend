<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if categories already exist (to avoid duplicates)
        if (Category::where('slug', 'like', 'electronics-%')->exists()) {
            return;
        }
        
        // Create some categories with unique slugs and direct image paths
        $category1 = Category::firstOrCreate(
            ['slug' => 'electronics-' . time()],
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'image' => null, // Direct image storage
                'is_active' => true,
            ]
        );
        
        $category2 = Category::firstOrCreate(
            ['slug' => 'clothing-' . time()],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and clothing items',
                'image' => null, // Direct image storage
                'is_active' => true,
            ]
        );
        
        // Create some products with direct image storage
        Product::firstOrCreate(
            ['slug' => 'smartphone'],
            [
                'name' => 'Smartphone',
                'description' => 'Latest model smartphone with advanced features',
                'mrp' => 599.99,
                'selling_price' => 499.99,
                'in_stock' => true,
                'stock_quantity' => 50,
                'status' => 'active',
                'main_photo' => null, // Direct image storage
                'product_gallery' => [],
            ]
        );
        
        Product::firstOrCreate(
            ['slug' => 'laptop'],
            [
                'name' => 'Laptop',
                'description' => 'High performance laptop for work and gaming',
                'mrp' => 1299.99,
                'selling_price' => 1099.99,
                'in_stock' => true,
                'stock_quantity' => 25,
                'status' => 'active',
                'main_photo' => null, // Direct image storage
                'product_gallery' => [],
            ]
        );
    }
}