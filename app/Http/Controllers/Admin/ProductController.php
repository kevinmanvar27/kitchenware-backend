<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ProductAttribute;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class ProductController extends Controller
{
    use LogsActivity;
    
    /**
     * Ensure product storage directories exist
     */
    private function ensureStorageDirectoriesExist()
    {
        $directories = [
            storage_path('app/public/products'),
            storage_path('app/public/products/gallery'),
            storage_path('app/public/products/variations'),
        ];
        
        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                mkdir($directory, 0775, true);
                Log::info('Created storage directory', ['path' => $directory]);
            }
        }
    }
    
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $this->authorize('viewAny', Product::class);
        
        $products = Product::latest()->paginate(10);
        
        // Get low stock products count for alert badge (handles both simple and variable products)
        $lowStockCount = $this->getLowStockCount();
        
        return view('admin.products.index', compact('products', 'lowStockCount'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $this->authorize('create', Product::class);
        
        // Get all active categories with their subcategories
        $categories = Category::with('subCategories')->where('is_active', true)->get();
        
        // Get all active attributes with their values
        $attributes = ProductAttribute::with('values')->active()->orderBy('sort_order')->get();
        
        return view('admin.products.create', compact('categories', 'attributes'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);
        
        // Ensure storage directories exist
        $this->ensureStorageDirectoriesExist();
        
        // Log the request data for debugging
        Log::info('Product store request data:', $request->except(['main_photo', 'gallery_images']));
        Log::info('Has main_photo file:', [
            'has_file' => $request->hasFile('main_photo'),
            'file_info' => $request->hasFile('main_photo') ? [
                'name' => $request->file('main_photo')->getClientOriginalName(),
                'size' => $request->file('main_photo')->getSize(),
                'mime' => $request->file('main_photo')->getMimeType(),
            ] : null
        ]);
        Log::info('Has gallery_images files:', [
            'has_files' => $request->hasFile('gallery_images'),
            'count' => $request->hasFile('gallery_images') ? count($request->file('gallery_images')) : 0
        ]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'description' => 'nullable|string',
            'mrp' => 'required_if:product_type,simple|nullable|numeric|min:0.01',
            'selling_price' => 'nullable|numeric|lt:mrp',
            'in_stock' => 'required_if:product_type,simple|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_categories' => 'nullable|array',
            'product_categories.*.category_id' => 'required|exists:categories,id',
            'product_categories.*.subcategory_ids' => 'nullable|array',
            'product_categories.*.subcategory_ids.*' => 'nullable|exists:sub_categories,id',
            'product_attributes' => 'required_if:product_type,variable|nullable|array',
            'variations' => 'required_if:product_type,variable|nullable|array|min:1',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.sku' => 'nullable|string',
            'variations.*.mrp' => 'nullable|numeric|min:0',
            'variations.*.selling_price' => 'nullable|numeric',
            'variations.*.stock_quantity' => 'required_with:variations|integer|min:0',
            'variations.*.low_quantity_threshold' => 'nullable|integer|min:0',
            'variations.*.attribute_values' => 'required_with:variations|array|min:1',
            'variations.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variations.*.remove_image' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $data = $request->only([
                'name', 'product_type', 'description', 'mrp', 'selling_price', 'in_stock', 
                'status', 'meta_title', 
                'meta_description', 'meta_keywords'
            ]);
            
            // Log the data that will be saved
            Log::info('Product data to be saved:', $data);
            
            // Handle product type - default to simple if not provided
            $data['product_type'] = $request->product_type ?? 'simple';
            
            // For simple products, handle stock quantity
            if ($data['product_type'] === 'simple') {
                $data['stock_quantity'] = $request->in_stock ? ($request->stock_quantity ?? 0) : 0;
                
                // Ensure stock_quantity is never null
                if (!isset($data['stock_quantity']) || is_null($data['stock_quantity'])) {
                    $data['stock_quantity'] = 0;
                }
            } else {
                // For variable products, set default values
                $data['stock_quantity'] = 0;
                $data['in_stock'] = true;
                $data['mrp'] = $data['mrp'] ?? 0;
            }
            
            // Handle low quantity threshold - default to 10 if not provided
            $data['low_quantity_threshold'] = $request->low_quantity_threshold ?? 10;
            
            // Handle main photo upload
            if ($request->hasFile('main_photo')) {
                $image = $request->file('main_photo');
                
                // Validate the file
                if ($image->isValid()) {
                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                    
                    // Store the file
                    $path = $image->storeAs('products', $filename, 'public');
                    
                    if ($path) {
                        $data['main_photo'] = $path;
                        Log::info('Main photo uploaded successfully', ['path' => $path]);
                    } else {
                        Log::error('Failed to store main photo');
                    }
                } else {
                    Log::error('Invalid main photo file', ['error' => $image->getError()]);
                }
            } else {
                Log::info('No main photo file in request');
            }
            
            // Handle product gallery upload
            $gallery = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    if ($image->isValid()) {
                        $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                        $path = $image->storeAs('products/gallery', $filename, 'public');
                        
                        if ($path) {
                            $gallery[] = $path;
                            Log::info('Gallery image uploaded successfully', ['path' => $path]);
                        } else {
                            Log::error('Failed to store gallery image');
                        }
                    } else {
                        Log::error('Invalid gallery image file', ['error' => $image->getError()]);
                    }
                }
            } else {
                Log::info('No gallery images in request');
            }
            $data['product_gallery'] = $gallery;
            
            // Handle product categories - convert from JSON string to array if needed
            $productCategories = $request->product_categories;
            if (is_string($productCategories)) {
                $productCategories = json_decode($productCategories, true);
            }
            $data['product_categories'] = is_array($productCategories) ? $productCategories : [];
            
            // Handle product attributes for variable products
            $productAttributes = $request->product_attributes;
            if (is_string($productAttributes)) {
                $productAttributes = json_decode($productAttributes, true);
            }
            $data['product_attributes'] = is_array($productAttributes) ? $productAttributes : [];
            
            // Log the final data before creating the product
            Log::info('Final product data before creation:', $data);
            
            $product = Product::create($data);
            
            // Handle variations for variable products
            if ($data['product_type'] === 'variable' && $request->has('variations')) {
                $variations = $request->variations;
                if (is_string($variations)) {
                    $variations = json_decode($variations, true);
                }
                
                if (is_array($variations) && !empty($variations)) {
                    $seenCombinations = [];
                    
                    foreach ($variations as $index => $variationData) {
                        // Convert attribute_values if it's a string
                        if (isset($variationData['attribute_values']) && is_string($variationData['attribute_values'])) {
                            $variationData['attribute_values'] = json_decode($variationData['attribute_values'], true);
                        }
                        
                        // Check for duplicate combinations
                        if (isset($variationData['attribute_values'])) {
                            ksort($variationData['attribute_values']);
                            $combinationKey = json_encode($variationData['attribute_values']);
                            
                            if (in_array($combinationKey, $seenCombinations)) {
                                Log::warning('Skipping duplicate variation combination', ['combination' => $variationData['attribute_values']]);
                                continue;
                            }
                            
                            $seenCombinations[] = $combinationKey;
                        }
                        
                        // Ensure stock_quantity is set and not null
                        if (!isset($variationData['stock_quantity']) || $variationData['stock_quantity'] === null || $variationData['stock_quantity'] === '') {
                            $variationData['stock_quantity'] = 0;
                        }
                        
                        // Set in_stock based on stock_quantity
                        $variationData['in_stock'] = isset($variationData['stock_quantity']) && $variationData['stock_quantity'] > 0;
                        
                        // Handle variation image upload
                        if ($request->hasFile("variations.{$index}.image")) {
                            $imageFile = $request->file("variations.{$index}.image");
                            
                            if ($imageFile->isValid()) {
                                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $imageFile->getClientOriginalName());
                                $path = $imageFile->storeAs('products/variations', $filename, 'public');
                                
                                if ($path) {
                                    $variationData['image'] = $path;
                                    Log::info('Variation image uploaded successfully', ['path' => $path]);
                                } else {
                                    Log::error('Failed to store variation image');
                                }
                            } else {
                                Log::error('Invalid variation image file', ['error' => $imageFile->getError()]);
                            }
                        }
                        
                        // Set first variation as default if not specified
                        if (!isset($variationData['is_default'])) {
                            $variationData['is_default'] = ($index === 0);
                        }
                        
                        $product->variations()->create($variationData);
                    }
                }
            }
            
            // Log the created product
            Log::info('Product created:', $product->toArray());
            
            // Log activity
            $this->logAdminActivity('created', "Created product: {$product->name}", $product);
            
            DB::commit();
            
            return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);
        
        return view('admin.products.show', compact('product'));
    }

    /**
     * Display the specified product details for modal view.
     */
    public function showDetails(Product $product)
    {
        $this->authorize('view', $product);
        
        // Refresh the product to get the latest data from database
        $product->refresh();
        
        // Load variations for variable products
        if ($product->isVariable()) {
            $product->load('variations');
        }
        
        // Return only the content section for the modal without extending layout
        return view('admin.products._product_details', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        
        // Load the variations
        $product->load('variations');
        
        // Get all active categories with their subcategories
        $categories = Category::with('subCategories')->where('is_active', true)->get();
        
        // Get all active attributes with their values
        $attributes = ProductAttribute::with('values')->active()->orderBy('sort_order')->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'attributes'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        
        // Ensure storage directories exist
        $this->ensureStorageDirectoriesExist();
        
        // Log the request data for debugging
        Log::info('Product update request data:', $request->except(['main_photo', 'gallery_images']));
        Log::info('Has main_photo file:', [
            'has_file' => $request->hasFile('main_photo'),
            'file_info' => $request->hasFile('main_photo') ? [
                'name' => $request->file('main_photo')->getClientOriginalName(),
                'size' => $request->file('main_photo')->getSize(),
                'mime' => $request->file('main_photo')->getMimeType(),
            ] : null
        ]);
        Log::info('Has gallery_images files:', [
            'has_files' => $request->hasFile('gallery_images'),
            'count' => $request->hasFile('gallery_images') ? count($request->file('gallery_images')) : 0
        ]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'description' => 'nullable|string',
            'mrp' => 'required_if:product_type,simple|nullable|numeric|min:0.01',
            'selling_price' => 'nullable|numeric|lt:mrp',
            'in_stock' => 'required_if:product_type,simple|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_categories' => 'nullable|array',
            'product_categories.*.category_id' => 'required|exists:categories,id',
            'product_categories.*.subcategory_ids' => 'nullable|array',
            'product_categories.*.subcategory_ids.*' => 'nullable|exists:sub_categories,id',
            'product_attributes' => 'required_if:product_type,variable|nullable|array',
            'variations' => 'required_if:product_type,variable|nullable|array|min:1',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.sku' => 'nullable|string',
            'variations.*.mrp' => 'nullable|numeric|min:0',
            'variations.*.selling_price' => 'nullable|numeric',
            'variations.*.stock_quantity' => 'required_with:variations|integer|min:0',
            'variations.*.low_quantity_threshold' => 'nullable|integer|min:0',
            'variations.*.attribute_values' => 'required_with:variations|array|min:1',
            'variations.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variations.*.remove_image' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            $data = $request->only([
                'name', 'product_type', 'description', 'mrp', 'selling_price', 'in_stock', 
                'status', 'meta_title', 
                'meta_description', 'meta_keywords'
            ]);
            
            // Log the data that will be saved
            Log::info('Product data to be updated:', $data);
            
            // Handle product type
            $data['product_type'] = $request->product_type ?? $product->product_type ?? 'simple';
            
            // For simple products, handle stock quantity
            if ($data['product_type'] === 'simple') {
                $data['stock_quantity'] = $request->in_stock ? ($request->stock_quantity ?? 0) : 0;
                
                // Ensure stock_quantity is never null
                if (!isset($data['stock_quantity']) || is_null($data['stock_quantity'])) {
                    $data['stock_quantity'] = 0;
                }
            } else {
                // For variable products, set default values
                $data['stock_quantity'] = 0;
                $data['in_stock'] = true;
                if (!isset($data['mrp']) || is_null($data['mrp'])) {
                    $data['mrp'] = $product->mrp ?? 0;
                }
            }
            
            // Handle low quantity threshold - keep existing value if not provided
            $data['low_quantity_threshold'] = $request->low_quantity_threshold ?? $product->low_quantity_threshold ?? 10;
            
            // Handle main photo upload
            if ($request->hasFile('main_photo')) {
                $image = $request->file('main_photo');
                
                // Validate the file
                if ($image->isValid()) {
                    // Delete old image - use raw attribute value
                    $oldMainPhoto = $product->getAttributes()['main_photo'] ?? null;
                    if ($oldMainPhoto && Storage::disk('public')->exists($oldMainPhoto)) {
                        Storage::disk('public')->delete($oldMainPhoto);
                        Log::info('Deleted old main photo', ['path' => $oldMainPhoto]);
                    }
                    
                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                    
                    // Store the file
                    $path = $image->storeAs('products', $filename, 'public');
                    
                    if ($path) {
                        $data['main_photo'] = $path;
                        Log::info('Main photo uploaded successfully', ['path' => $path]);
                    } else {
                        Log::error('Failed to store main photo');
                    }
                } else {
                    Log::error('Invalid main photo file', ['error' => $image->getError()]);
                }
            }
            
            // Handle product gallery upload
            if ($request->hasFile('gallery_images')) {
                // Delete old gallery images
                if (!empty($product->product_gallery)) {
                    foreach ($product->product_gallery as $oldPath) {
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                            Log::info('Deleted old gallery image', ['path' => $oldPath]);
                        }
                    }
                }
                
                $gallery = [];
                foreach ($request->file('gallery_images') as $image) {
                    if ($image->isValid()) {
                        $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                        $path = $image->storeAs('products/gallery', $filename, 'public');
                        
                        if ($path) {
                            $gallery[] = $path;
                            Log::info('Gallery image uploaded successfully', ['path' => $path]);
                        } else {
                            Log::error('Failed to store gallery image');
                        }
                    } else {
                        Log::error('Invalid gallery image file', ['error' => $image->getError()]);
                    }
                }
                $data['product_gallery'] = $gallery;
            } else {
                // Keep existing gallery if no new images uploaded
                $data['product_gallery'] = $product->product_gallery ?? [];
            }
            
            // Handle product categories - convert from JSON string to array if needed
            $productCategories = $request->product_categories;
            if (is_string($productCategories)) {
                $productCategories = json_decode($productCategories, true);
            }
            $data['product_categories'] = is_array($productCategories) ? $productCategories : [];
            
            // Handle product attributes for variable products
            $productAttributes = $request->product_attributes;
            if (is_string($productAttributes)) {
                $productAttributes = json_decode($productAttributes, true);
            }
            $data['product_attributes'] = is_array($productAttributes) ? $productAttributes : [];
            
            // Log the final data before updating the product
            Log::info('Final product data before update:', $data);
            
            $product->update($data);
            
            // Handle variations for variable products
            if ($data['product_type'] === 'variable' && $request->has('variations')) {
                $variations = $request->variations;
                if (is_string($variations)) {
                    $variations = json_decode($variations, true);
                }
                
                if (is_array($variations)) {
                    // Get existing variation IDs
                    $existingVariationIds = $product->variations()->pluck('id')->toArray();
                    $updatedVariationIds = [];
                    $seenCombinations = [];
                    
                    foreach ($variations as $index => $variationData) {
                        // Skip variations marked for deletion
                        if (isset($variationData['_delete']) && $variationData['_delete'] == '1') {
                            if (isset($variationData['id']) && !empty($variationData['id'])) {
                                // Add to deletion list
                                $updatedVariationIds[] = $variationData['id']; // Don't add to updated list
                                
                                // Delete the variation and its image
                                $variation = ProductVariation::find($variationData['id']);
                                if ($variation && $variation->product_id == $product->id) {
                                    if ($variation->image_id) {
                                        $media = Media::find($variation->image_id);
                                        if ($media) {
                                            Storage::disk('public')->delete($media->path);
                                            $media->delete();
                                        }
                                    }
                                    $variation->delete();
                                }
                            }
                            continue;
                        }
                        
                        // Convert attribute_values if it's a string
                        if (isset($variationData['attribute_values']) && is_string($variationData['attribute_values'])) {
                            $variationData['attribute_values'] = json_decode($variationData['attribute_values'], true);
                        }
                        
                        // Check for duplicate combinations (skip for existing variations being updated)
                        if (isset($variationData['attribute_values'])) {
                            ksort($variationData['attribute_values']);
                            $combinationKey = json_encode($variationData['attribute_values']);
                            
                            // Only check duplicates for new variations
                            if (!isset($variationData['id']) || empty($variationData['id'])) {
                                if (in_array($combinationKey, $seenCombinations)) {
                                    Log::warning('Skipping duplicate variation combination', ['combination' => $variationData['attribute_values']]);
                                    continue;
                                }
                            }
                            
                            $seenCombinations[] = $combinationKey;
                        }
                        
                        // Ensure stock_quantity is set and not null
                        if (!isset($variationData['stock_quantity']) || $variationData['stock_quantity'] === null || $variationData['stock_quantity'] === '') {
                            $variationData['stock_quantity'] = 0;
                        }
                        
                        // Set in_stock based on stock_quantity
                        $variationData['in_stock'] = isset($variationData['stock_quantity']) && $variationData['stock_quantity'] > 0;
                        
                        // Handle variation image upload
                        if ($request->hasFile("variations.{$index}.image")) {
                            $imageFile = $request->file("variations.{$index}.image");
                            
                            if ($imageFile->isValid()) {
                                // Delete old image if exists
                                if (isset($variationData['id'])) {
                                    $existingVariation = ProductVariation::find($variationData['id']);
                                    if ($existingVariation && $existingVariation->image) {
                                        if (Storage::disk('public')->exists($existingVariation->image)) {
                                            Storage::disk('public')->delete($existingVariation->image);
                                            Log::info('Deleted old variation image', ['path' => $existingVariation->image]);
                                        }
                                    }
                                }
                                
                                $filename = time() . '_' . uniqid() . '_' . str_replace(' ', '_', $imageFile->getClientOriginalName());
                                $path = $imageFile->storeAs('products/variations', $filename, 'public');
                                
                                if ($path) {
                                    $variationData['image'] = $path;
                                    Log::info('Variation image uploaded successfully', ['path' => $path]);
                                } else {
                                    Log::error('Failed to store variation image');
                                }
                            } else {
                                Log::error('Invalid variation image file', ['error' => $imageFile->getError()]);
                            }
                        }
                        
                        // Handle image removal
                        if (isset($variationData['remove_image']) && $variationData['remove_image'] == '1') {
                            if (isset($variationData['id'])) {
                                $existingVariation = ProductVariation::find($variationData['id']);
                                if ($existingVariation && $existingVariation->image) {
                                    if (Storage::disk('public')->exists($existingVariation->image)) {
                                        Storage::disk('public')->delete($existingVariation->image);
                                    }
                                }
                            }
                            $variationData['image'] = null;
                        }
                        
                        // Remove temporary fields that shouldn't be saved to database
                        unset($variationData['remove_image']);
                        
                        if (isset($variationData['id']) && !empty($variationData['id'])) {
                            // Update existing variation
                            $variation = ProductVariation::find($variationData['id']);
                            if ($variation && $variation->product_id == $product->id) {
                                $variation->update($variationData);
                                $updatedVariationIds[] = $variation->id;
                            }
                        } else {
                            // Create new variation
                            // Set first variation as default if no default exists
                            if (!isset($variationData['is_default'])) {
                                $variationData['is_default'] = ($index === 0 && empty($existingVariationIds));
                            }
                            
                            $newVariation = $product->variations()->create($variationData);
                            $updatedVariationIds[] = $newVariation->id;
                        }
                    }
                    
                    // Delete variations that were removed
                    $variationsToDelete = array_diff($existingVariationIds, $updatedVariationIds);
                    if (!empty($variationsToDelete)) {
                        // Delete associated images first
                        $variationsToDeleteModels = ProductVariation::whereIn('id', $variationsToDelete)->get();
                        foreach ($variationsToDeleteModels as $variationToDelete) {
                            if ($variationToDelete->image && Storage::disk('public')->exists($variationToDelete->image)) {
                                Storage::disk('public')->delete($variationToDelete->image);
                            }
                        }
                        
                        // Now delete the variations
                        ProductVariation::whereIn('id', $variationsToDelete)->delete();
                    }
                }
            }
            
            // Log the updated product
            Log::info('Product updated:', $product->toArray());
            
            // Log activity
            $this->logAdminActivity('updated', "Updated product: {$product->name}", $product);
            
            DB::commit();
            
            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        $productName = $product->name;
        $productId = $product->id;
        
        // Delete main photo - use raw attribute value
        $mainPhoto = $product->getAttributes()['main_photo'] ?? null;
        if ($mainPhoto && Storage::disk('public')->exists($mainPhoto)) {
            Storage::disk('public')->delete($mainPhoto);
        }
        
        // Delete gallery images
        if ($product->product_gallery) {
            $galleryImages = is_string($product->product_gallery) ? json_decode($product->product_gallery, true) : $product->product_gallery;
            if (is_array($galleryImages)) {
                foreach ($galleryImages as $imagePath) {
                    if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                }
            }
        }
        
        // Delete variation images
        foreach ($product->variations as $variation) {
            if ($variation->image && Storage::disk('public')->exists($variation->image)) {
                Storage::disk('public')->delete($variation->image);
            }
        }
        
        $product->delete();
        
        // Log activity
        $this->logAdminActivity('deleted', "Deleted product: {$productName} (ID: {$productId})");
        
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Display products with low stock.
     */
    public function lowStock()
    {
        $this->authorize('viewAny', Product::class);
        
        // Get all products with their variations
        $allProducts = Product::with(['variations'])->get();
        
        // Filter products that have low stock
        $lowStockProducts = $allProducts->filter(function ($product) {
            if ($product->isVariable()) {
                // For variable products, check if any variation has low stock
                foreach ($product->variations as $variation) {
                    $threshold = $variation->low_quantity_threshold ?? $product->low_quantity_threshold ?? 10;
                    // Include variations with 0 stock or stock <= threshold
                    if ($variation->stock_quantity <= $threshold) {
                        return true;
                    }
                }
                return false;
            } else {
                // For simple products, check if stock <= threshold
                $threshold = $product->low_quantity_threshold ?? 10;
                return $product->stock_quantity <= $threshold;
            }
        });
        
        // Return all low stock products (DataTables will handle pagination)
        $paginatedProducts = $lowStockProducts->values();
        
        return view('admin.products.low-stock', compact('paginatedProducts'));
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroyMedia(Media $media)
    {
        try {
            // Delete the file from storage
            Storage::disk('public')->delete($media->path);
            
            // Delete the media record
            $media->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the count of low stock products.
     * Handles both simple and variable products.
     *
     * @return int
     */
    private function getLowStockCount()
    {
        $allProducts = Product::with('variations')->get();
        
        return $allProducts->filter(function ($product) {
            if ($product->isVariable()) {
                // For variable products, check if any variation has low stock
                foreach ($product->variations as $variation) {
                    $threshold = $variation->low_quantity_threshold ?? $product->low_quantity_threshold ?? 10;
                    if ($variation->stock_quantity <= $threshold) {
                        return true;
                    }
                }
                return false;
            } else {
                // For simple products, check if stock <= threshold
                $threshold = $product->low_quantity_threshold ?? 10;
                return $product->stock_quantity <= $threshold;
            }
        })->count();
    }
}