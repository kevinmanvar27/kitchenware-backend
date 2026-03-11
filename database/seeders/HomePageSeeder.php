<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Media;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if we already have media and categories seeded
        if (Media::count() > 0 && Category::count() > 0 && Product::count() > 0) {
            $this->command->info('Home page data already seeded. Skipping...');
            return;
        }

        // Create some media items for categories and products
        $media1 = Media::factory()->create([
            'name' => 'Category 1 Image',
            'file_name' => 'category1.jpg',
            'mime_type' => 'image/jpeg',
            'path' => 'media/category1.jpg',
        ]);
        
        $media2 = Media::factory()->create([
            'name' => 'Category 2 Image',
            'file_name' => 'category2.jpg',
            'mime_type' => 'image/jpeg',
            'path' => 'media/category2.jpg',
        ]);
        
        $media3 = Media::factory()->create([
            'name' => 'Product 1 Image',
            'file_name' => 'product1.jpg',
            'mime_type' => 'image/jpeg',
            'path' => 'media/product1.jpg',
        ]);
        
        // Get existing categories or create new ones with unique slugs
        $category1 = Category::where('slug', 'electronics')->first();
        if (!$category1) {
            $category1 = Category::factory()->create([
                'name' => 'Electronics',
                'slug' => 'electronics-homepage-' . time(),
                'description' => 'Electronic devices and gadgets',
                'image_id' => $media1->id,
                'is_active' => true,
            ]);
        }
        
        $category2 = Category::where('slug', 'clothing')->first();
        if (!$category2) {
            $category2 = Category::factory()->create([
                'name' => 'Clothing',
                'slug' => 'clothing-homepage-' . time(),
                'description' => 'Fashion and clothing items',
                'image_id' => $media2->id,
                'is_active' => true,
            ]);
        }
        
        // Create some products only if they don't exist
        if (!Product::where('name', 'Smartphone')->exists()) {
            Product::factory()->create([
                'name' => 'Smartphone',
                'description' => 'Latest model smartphone with advanced features',
                'mrp' => 599.99,
                'selling_price' => 499.99,
                'in_stock' => true,
                'stock_quantity' => 50,
                'status' => 'active',
                'main_photo_id' => $media3->id,
            ]);
        }
        
        if (!Product::where('name', 'Laptop')->exists()) {
            Product::factory()->create([
                'name' => 'Laptop',
                'description' => 'High performance laptop for work and gaming',
                'mrp' => 1299.99,
                'selling_price' => 1099.99,
                'in_stock' => true,
                'stock_quantity' => 25,
                'status' => 'active',
                'main_photo_id' => $media3->id,
            ]);
        }
    }
}