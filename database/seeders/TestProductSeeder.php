<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Media;
use Illuminate\Support\Str;

class TestProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test product with a main photo
        $media = Media::first();
        if ($media) {
            Product::create([
                'name' => 'Test Product with Image',
                'slug' => Str::slug('Test Product with Image'),
                'description' => 'This is a test product to verify image display',
                'mrp' => 100.00,
                'selling_price' => 80.00,
                'in_stock' => true,
                'stock_quantity' => 10,
                'status' => 'published',
                'main_photo_id' => $media->id,
                'product_gallery' => [],
            ]);
        }
    }
}