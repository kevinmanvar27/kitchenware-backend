<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories
        $categories = Category::all();
        
        // Get all products
        $products = Product::all();
        
        // Associate each product with all categories (for testing purposes)
        foreach ($products as $product) {
            $categoryData = [];
            foreach ($categories as $category) {
                $categoryData[] = [
                    'category_id' => $category->id,
                    'subcategory_ids' => $category->subCategories->pluck('id')->toArray()
                ];
            }
            
            $product->product_categories = $categoryData;
            $product->save();
        }
    }
}