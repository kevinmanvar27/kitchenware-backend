<?php

namespace App\Http\Controllers\Vendor;

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
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class ProductController extends Controller
{
    use LogsActivity;
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->getActiveVendor();
    }

    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        $products = Product::where('vendor_id', $vendor->id)
            ->latest()
            ->paginate(10);
        
        // Get low stock products count for alert badge (handles both simple and variable products)
        $lowStockCount = $this->getLowStockCount($vendor->id);
        
        return view('vendor.products.index', compact('products', 'lowStockCount'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $vendor = $this->getVendor();
        
        // Get only vendor's own categories
        $categories = Category::with('subCategories')
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->get();
        
        // Get only vendor's own attributes with their values
        $attributes = ProductAttribute::with('values')
            ->where('vendor_id', $vendor->id)
            ->orderBy('sort_order')
            ->get();
        
        return view('vendor.products.create', compact('categories', 'attributes'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $vendor = $this->getVendor();
        
        $productType = $request->input('product_type', 'simple');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'description' => 'nullable|string',
            'mrp' => $productType === 'simple' ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0',
            'selling_price' => $productType === 'simple' ? 'nullable|numeric|lte:mrp' : 'nullable|numeric|min:0',
            'in_stock' => 'nullable|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_main_photo' => 'nullable|boolean',
            'existing_gallery' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_categories' => 'nullable|array',
            'product_categories.*.category_id' => 'required|exists:categories,id',
            'product_categories.*.subcategory_ids' => 'nullable|array',
            'product_categories.*.subcategory_ids.*' => 'nullable|exists:sub_categories,id',
            'product_attributes' => $productType === 'variable' ? 'required|array|min:1' : 'nullable|array',
            'variations' => $productType === 'variable' ? 'required|array|min:1' : 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.sku' => 'nullable|string',
            'variations.*.mrp' => 'nullable|numeric|min:0',
            'variations.*.selling_price' => 'nullable|numeric|min:0',
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
            
            // Handle main photo upload
            if ($request->hasFile('main_photo')) {
                $mainPhoto = $request->file('main_photo');
                $data['main_photo'] = $mainPhoto->store('vendor_' . $vendor->id . '/products', 'public');
            }
            
            // Assign vendor_id
            $data['vendor_id'] = $vendor->id;
            
            // Handle product type - default to simple if not provided
            $data['product_type'] = $request->product_type ?? 'simple';
            
            // For simple products, handle stock quantity
            if ($data['product_type'] === 'simple') {
                $data['stock_quantity'] = $request->in_stock ? ($request->stock_quantity ?? 0) : 0;
                
                if (!isset($data['stock_quantity']) || is_null($data['stock_quantity'])) {
                    $data['stock_quantity'] = 0;
                }
            } else {
                $data['stock_quantity'] = 0;
                $data['in_stock'] = true;
                $data['mrp'] = $data['mrp'] ?? 0;
            }
            
            $data['low_quantity_threshold'] = $request->low_quantity_threshold ?? 10;
            
            // Handle product gallery (array of image paths)
            // Start with existing gallery images
            $galleryPaths = [];
            if ($request->has('existing_gallery')) {
                $existingGallery = json_decode($request->existing_gallery, true);
                if (is_array($existingGallery)) {
                    $galleryPaths = $existingGallery;
                }
            }
            
            // Add newly uploaded gallery images
            if ($request->hasFile('gallery_images')) {
                $galleryImages = $request->file('gallery_images');
                if (is_array($galleryImages)) {
                    foreach ($galleryImages as $galleryImage) {
                        $path = $galleryImage->store('vendor_' . $vendor->id . '/products/gallery', 'public');
                        $galleryPaths[] = $path;
                    }
                }
            }
            
            // Always set product_gallery (even if empty array)
            $data['product_gallery'] = $galleryPaths;
            
            // Handle product categories
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
                        if (isset($variationData['attribute_values']) && is_string($variationData['attribute_values'])) {
                            $variationData['attribute_values'] = json_decode($variationData['attribute_values'], true);
                        }
                        
                        if (isset($variationData['attribute_values'])) {
                            ksort($variationData['attribute_values']);
                            $combinationKey = json_encode($variationData['attribute_values']);
                            
                            if (in_array($combinationKey, $seenCombinations)) {
                                continue;
                            }
                            
                            $seenCombinations[] = $combinationKey;
                        }
                        
                        if (!isset($variationData['stock_quantity']) || $variationData['stock_quantity'] === null || $variationData['stock_quantity'] === '') {
                            $variationData['stock_quantity'] = 0;
                        }
                        
                        $variationData['in_stock'] = isset($variationData['stock_quantity']) && $variationData['stock_quantity'] > 0;
                        
                        // Handle variation image upload
                        $imagePath = null;
                        if ($request->hasFile("variations.{$index}.image")) {
                            $imageFile = $request->file("variations.{$index}.image");
                            $imagePath = $imageFile->store('vendor_' . $vendor->id . '/variations', 'public');
                        }
                        
                        ProductVariation::create([
                            'product_id' => $product->id,
                            'sku' => $variationData['sku'] ?? null,
                            'mrp' => $variationData['mrp'] ?? 0,
                            'selling_price' => $variationData['selling_price'] ?? null,
                            'stock_quantity' => $variationData['stock_quantity'],
                            'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                            'in_stock' => $variationData['in_stock'],
                            'attribute_values' => $variationData['attribute_values'],
                            'image' => $imagePath,
                            'is_default' => $index === 0,
                        ]);
                    }
                }
            }
            
            // Log activity
            $this->logVendorActivity($vendor->id, 'created', "Created product: {$product->name}", $product);
            
            DB::commit();
            
            return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating product: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $product->load(['variations']);
        
        return view('vendor.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $product->load(['variations']);
        
        // Get only vendor's own categories
        $categories = Category::with('subCategories')
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->get();
        
        // Get only vendor's own attributes with their values
        $attributes = ProductAttribute::with('values')
            ->where('vendor_id', $vendor->id)
            ->orderBy('sort_order')
            ->get();
        
        return view('vendor.products.edit', compact('product', 'categories', 'attributes'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $productType = $request->input('product_type', 'simple');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'description' => 'nullable|string',
            'mrp' => $productType === 'simple' ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0',
            'selling_price' => $productType === 'simple' ? 'nullable|numeric|lte:mrp' : 'nullable|numeric|min:0',
            'in_stock' => 'nullable|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_quantity_threshold' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_main_photo' => 'nullable|boolean',
            'existing_gallery' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_categories' => 'nullable|array',
            'product_attributes' => $productType === 'variable' ? 'required|array|min:1' : 'nullable|array',
            'variations' => $productType === 'variable' ? 'required|array|min:1' : 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.sku' => 'nullable|string',
            'variations.*.mrp' => 'nullable|numeric|min:0',
            'variations.*.selling_price' => 'nullable|numeric|min:0',
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
            
            // Handle main photo upload
            if ($request->hasFile('main_photo')) {
                // Delete old main photo if exists - use raw attribute value
                $oldMainPhoto = $product->getAttributes()['main_photo'] ?? null;
                if ($oldMainPhoto && Storage::disk('public')->exists($oldMainPhoto)) {
                    Storage::disk('public')->delete($oldMainPhoto);
                }
                $mainPhoto = $request->file('main_photo');
                $data['main_photo'] = $mainPhoto->store('vendor_' . $vendor->id . '/products', 'public');
            } elseif ($request->input('remove_main_photo') == '1') {
                // User explicitly removed the main photo - use raw attribute value
                $oldMainPhoto = $product->getAttributes()['main_photo'] ?? null;
                if ($oldMainPhoto && Storage::disk('public')->exists($oldMainPhoto)) {
                    Storage::disk('public')->delete($oldMainPhoto);
                }
                $data['main_photo'] = null;
            }
            // If neither hasFile nor remove flag, keep existing main_photo (don't add to $data)
            
            $data['product_type'] = $request->product_type ?? 'simple';
            
            if ($data['product_type'] === 'simple') {
                $data['stock_quantity'] = $request->in_stock ? ($request->stock_quantity ?? 0) : 0;
            } else {
                $data['stock_quantity'] = 0;
                $data['in_stock'] = true;
            }
            
            $data['low_quantity_threshold'] = $request->low_quantity_threshold ?? 10;
            
            // Handle product gallery (array of image paths)
            // Start with existing gallery images
            $galleryPaths = [];
            if ($request->has('existing_gallery')) {
                $existingGallery = json_decode($request->existing_gallery, true);
                if (is_array($existingGallery)) {
                    $galleryPaths = $existingGallery;
                }
            }
            
            // Add newly uploaded gallery images
            if ($request->hasFile('gallery_images')) {
                $galleryImages = $request->file('gallery_images');
                if (is_array($galleryImages)) {
                    foreach ($galleryImages as $galleryImage) {
                        $path = $galleryImage->store('vendor_' . $vendor->id . '/products/gallery', 'public');
                        $galleryPaths[] = $path;
                    }
                }
            }
            
            // Always set product_gallery (even if empty array)
            $data['product_gallery'] = $galleryPaths;
            
            // Handle product categories
            $productCategories = $request->product_categories;
            if (is_string($productCategories)) {
                $productCategories = json_decode($productCategories, true);
            }
            $data['product_categories'] = is_array($productCategories) ? $productCategories : [];
            
            // Handle product attributes
            $productAttributes = $request->product_attributes;
            if (is_string($productAttributes)) {
                $productAttributes = json_decode($productAttributes, true);
            }
            $data['product_attributes'] = is_array($productAttributes) ? $productAttributes : [];
            
            $product->update($data);
            
            // Handle variations for variable products
            if ($data['product_type'] === 'variable' && $request->has('variations')) {
                $variations = $request->variations;
                if (is_string($variations)) {
                    $variations = json_decode($variations, true);
                }
                
                $existingVariationIds = $product->variations->pluck('id')->toArray();
                $updatedVariationIds = [];
                
                if (is_array($variations) && !empty($variations)) {
                    foreach ($variations as $index => $variationData) {
                        if (isset($variationData['attribute_values']) && is_string($variationData['attribute_values'])) {
                            $variationData['attribute_values'] = json_decode($variationData['attribute_values'], true);
                        }
                        
                        if (!isset($variationData['stock_quantity']) || $variationData['stock_quantity'] === null) {
                            $variationData['stock_quantity'] = 0;
                        }
                        
                        $variationData['in_stock'] = $variationData['stock_quantity'] > 0;
                        
                        // Handle variation image
                        $imagePath = null;
                        $shouldClearImage = false;
                        
                        // Check if remove_image flag is set
                        if (isset($variationData['remove_image']) && $variationData['remove_image'] == '1') {
                            $shouldClearImage = true;
                        } elseif ($request->hasFile("variations.{$index}.image")) {
                            $imageFile = $request->file("variations.{$index}.image");
                            $imagePath = $imageFile->store('vendor_' . $vendor->id . '/variations', 'public');
                        }
                        
                        if (isset($variationData['id']) && $variationData['id']) {
                            $variation = ProductVariation::find($variationData['id']);
                            if ($variation && $variation->product_id === $product->id) {
                                // Delete old image if uploading new one or clearing
                                if (($imagePath || $shouldClearImage) && $variation->image && Storage::disk('public')->exists($variation->image)) {
                                    Storage::disk('public')->delete($variation->image);
                                }
                                
                                // Determine final image path
                                $finalImagePath = $imagePath;
                                if ($finalImagePath === null && !$shouldClearImage) {
                                    $finalImagePath = $variation->image;
                                }
                                
                                $variation->update([
                                    'sku' => $variationData['sku'] ?? null,
                                    'mrp' => $variationData['mrp'] ?? 0,
                                    'selling_price' => $variationData['selling_price'] ?? null,
                                    'stock_quantity' => $variationData['stock_quantity'],
                                    'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                                    'in_stock' => $variationData['in_stock'],
                                    'attribute_values' => $variationData['attribute_values'],
                                    'image' => $finalImagePath,
                                    'is_default' => $index === 0,
                                ]);
                                $updatedVariationIds[] = $variation->id;
                            }
                        } else {
                            $newVariation = ProductVariation::create([
                                'product_id' => $product->id,
                                'sku' => $variationData['sku'] ?? null,
                                'mrp' => $variationData['mrp'] ?? 0,
                                'selling_price' => $variationData['selling_price'] ?? null,
                                'stock_quantity' => $variationData['stock_quantity'],
                                'low_quantity_threshold' => $variationData['low_quantity_threshold'] ?? 10,
                                'in_stock' => $variationData['in_stock'],
                                'attribute_values' => $variationData['attribute_values'],
                                'image' => $imagePath,
                                'is_default' => $index === 0,
                            ]);
                            $updatedVariationIds[] = $newVariation->id;
                        }
                    }
                }
                
                // Delete removed variations and their images
                $variationsToDelete = array_diff($existingVariationIds, $updatedVariationIds);
                if (!empty($variationsToDelete)) {
                    $variationsToDeleteModels = ProductVariation::whereIn('id', $variationsToDelete)->get();
                    foreach ($variationsToDeleteModels as $variationToDelete) {
                        if ($variationToDelete->image && Storage::disk('public')->exists($variationToDelete->image)) {
                            Storage::disk('public')->delete($variationToDelete->image);
                        }
                    }
                    ProductVariation::whereIn('id', $variationsToDelete)->delete();
                }
            }
            
            // Log activity
            $this->logVendorActivity($vendor->id, 'updated', "Updated product: {$product->name}", $product);
            
            DB::commit();
            
            return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $vendor = $this->getVendor();
        
        if ($product->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this product.');
        }
        
        $productName = $product->name;
        $productId = $product->id;
        
        try {
            // Delete main photo - use raw attribute value
            $mainPhoto = $product->getAttributes()['main_photo'] ?? null;
            if ($mainPhoto && Storage::disk('public')->exists($mainPhoto)) {
                Storage::disk('public')->delete($mainPhoto);
            }
            
            // Delete gallery images
            if ($product->gallery) {
                $galleryImages = is_string($product->gallery) ? json_decode($product->gallery, true) : $product->gallery;
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
            
            $product->variations()->delete();
            $product->delete();
            
            // Log activity
            $this->logVendorActivity($vendor->id, 'deleted', "Deleted product: {$productName} (ID: {$productId})");
            
            return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Display low stock products.
     */
    public function lowStock()
    {
        $vendor = $this->getVendor();
        
        // Get all products with their variations for this vendor
        $allProducts = Product::with(['variations'])
            ->where('vendor_id', $vendor->id)
            ->get();
        
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
        
        // Paginate the filtered results
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedProducts = new \Illuminate\Pagination\LengthAwarePaginator(
            $lowStockProducts->slice($offset, $perPage)->values(),
            $lowStockProducts->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        return view('vendor.products.low-stock', compact('paginatedProducts'));
    }

    /**
     * Get the count of low stock products for a vendor.
     * Handles both simple and variable products.
     *
     * @param int $vendorId
     * @return int
     */
    private function getLowStockCount($vendorId)
    {
        $allProducts = Product::with('variations')
            ->where('vendor_id', $vendorId)
            ->get();
        
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

    /**
     * Toggle featured status of a product
     */
    public function toggleFeatured(Product $product)
    {
        try {
            $vendor = $this->getVendor();
            
            // Ensure the product belongs to the vendor
            if ($product->vendor_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            
            // Toggle the featured status
            $product->is_featured = !$product->is_featured;
            $product->save();
            
            // Log activity
            $this->logVendorActivity(
                $vendor->id,
                'product_featured_toggled',
                "Toggled featured status for product: {$product->name} to " . ($product->is_featured ? 'ON' : 'OFF'),
                $product
            );
            
            return response()->json([
                'success' => true,
                'is_featured' => $product->is_featured,
                'message' => $product->is_featured ? 'Product marked as featured.' : 'Product removed from featured.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling featured status: ' . $e->getMessage(), [
                'product_id' => $product->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the featured status. Please try again.'
            ], 500);
        }
    }
}
