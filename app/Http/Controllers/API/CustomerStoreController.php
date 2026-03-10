<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\VendorCustomer;

/**
 * @OA\Tag(
 *     name="Customer Store",
 *     description="API Endpoints for Vendor Customers to browse their vendor's products"
 * )
 */
class CustomerStoreController extends Controller
{
    /**
     * Get the authenticated customer
     */
    private function getCustomer(Request $request): VendorCustomer
    {
        return $request->user();
    }

    /**
     * Helper method to check if a product belongs to a category
     */
    private function productBelongsToCategory($product, $categoryId): bool
    {
        if (!$product->product_categories || !is_array($product->product_categories)) {
            return false;
        }
        
        foreach ($product->product_categories as $catData) {
            if (isset($catData['category_id'])) {
                // Handle both int and string comparison
                if ((int)$catData['category_id'] === (int)$categoryId) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Helper method to check if a product belongs to a subcategory
     */
    private function productBelongsToSubcategory($product, $subcategoryId, $categoryId = null): bool
    {
        if (!$product->product_categories || !is_array($product->product_categories)) {
            return false;
        }
        
        foreach ($product->product_categories as $catData) {
            // If category filter is provided, check category first
            if ($categoryId !== null) {
                if (!isset($catData['category_id']) || (int)$catData['category_id'] !== (int)$categoryId) {
                    continue;
                }
            }
            
            // Check subcategory_ids - handle both array and single value
            if (isset($catData['subcategory_ids'])) {
                $subcategoryIds = $catData['subcategory_ids'];
                
                // Handle case where subcategory_ids might be a single value instead of array
                if (!is_array($subcategoryIds)) {
                    $subcategoryIds = [$subcategoryIds];
                }
                
                foreach ($subcategoryIds as $subId) {
                    if ((int)$subId === (int)$subcategoryId) {
                        return true;
                    }
                }
            }
            
            // Also check legacy 'subcategory_id' field (single value)
            if (isset($catData['subcategory_id'])) {
                if ((int)$catData['subcategory_id'] === (int)$subcategoryId) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/products",
     *     summary="Get vendor products",
     *     description="Get products from the customer's vendor only",
     *     operationId="getCustomerVendorProducts",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Search by product name", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", description="Filter by category", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="subcategory_id", in="query", description="Filter by subcategory", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="in_stock", in="query", description="Filter by stock status", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort by field (name, price, created_at)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order (asc, desc)", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function products(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        // Get all products from the customer's vendor
        $query = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->with(['vendor:id,store_name,store_slug', 'variations']);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by stock status
        if ($request->has('in_stock')) {
            $query->where('in_stock', filter_var($request->in_stock, FILTER_VALIDATE_BOOLEAN));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'selling_price', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Get all products first for filtering
        $allProducts = $query->get();
        
        // Apply category filter using collection filtering (handles JSON properly)
        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $allProducts = $allProducts->filter(function ($product) use ($categoryId) {
                return $this->productBelongsToCategory($product, $categoryId);
            });
        }

        // Apply subcategory filter using collection filtering
        if ($request->filled('subcategory_id')) {
            $subcategoryId = $request->subcategory_id;
            $categoryId = $request->filled('category_id') ? $request->category_id : null;
            $allProducts = $allProducts->filter(function ($product) use ($subcategoryId, $categoryId) {
                return $this->productBelongsToSubcategory($product, $subcategoryId, $categoryId);
            });
        }

        // Manual pagination
        $perPage = min($request->get('per_page', 20), 50);
        $page = $request->get('page', 1);
        $total = $allProducts->count();
        $paginatedProducts = $allProducts->forPage($page, $perPage)->values();

        // Transform products with customer discount
        $transformedProducts = $paginatedProducts->map(function($product) use ($customer) {
            $priceRange = $product->price_range;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type' => $product->product_type,
                'mrp' => $product->mrp,
                'selling_price' => $product->selling_price,
                'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                'customer_discount' => $customer->discount_percentage,
                'price_range' => [
                    'min' => $customer->getDiscountedPrice($priceRange['min']),
                    'max' => $customer->getDiscountedPrice($priceRange['max']),
                ],
                'in_stock' => $product->in_stock,
                'stock_quantity' => $product->stock_quantity,
                'main_photo_url' => $product->mainPhoto?->url,
                'has_variations' => $product->isVariable(),
                'variations_count' => $product->variations->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'data' => $transformedProducts,
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/products/{id}",
     *     summary="Get product details",
     *     description="Get detailed information about a specific product from the customer's vendor",
     *     operationId="getCustomerProductDetails",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Product ID or slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function productDetails(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        // Only get product from the customer's vendor
        $product = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->where(function($q) use ($id) {
                $q->where('id', $id)->orWhere('slug', $id);
            })
            ->with(['variations', 'vendor:id,store_name,store_slug'])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ], 404);
        }

        $priceRange = $product->price_range;

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'product_type' => $product->product_type,
            'mrp' => $product->mrp,
            'selling_price' => $product->selling_price,
            'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
            'customer_discount' => $customer->discount_percentage,
            'price_range' => [
                'min' => $customer->getDiscountedPrice($priceRange['min']),
                'max' => $customer->getDiscountedPrice($priceRange['max']),
            ],
            'in_stock' => $product->in_stock,
            'stock_quantity' => $product->stock_quantity,
            'main_photo_url' => $product->mainPhoto?->url,
            'gallery_photos' => collect($product->gallery_photos ?? [])->map(fn($photo) => $photo['url'] ?? null)->filter()->values()->toArray(),
            'categories' => $product->categories->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
            ]),
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
        ];

        // Add variations if variable product
        if ($product->isVariable()) {
            $productData['variations'] = $product->variations->map(function($variation) use ($customer) {
                return [
                    'id' => $variation->id,
                    'sku' => $variation->sku,
                    'attribute_values' => $variation->attribute_values,
                    'attributes' => $variation->formatted_attributes,
                    'mrp' => $variation->mrp,
                    'selling_price' => $variation->selling_price,
                    'discounted_price' => $customer->getDiscountedPrice($variation->selling_price ?? $variation->mrp),
                    'in_stock' => $variation->in_stock,
                    'stock_quantity' => $variation->stock_quantity,
                    'is_default' => $variation->is_default,
                    'image_url' => $variation->image_url,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $productData
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/categories",
     *     summary="Get vendor categories",
     *     description="Get categories that have products from the customer's vendor",
     *     operationId="getCustomerVendorCategories",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function categories(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        // Get all products from this vendor to find which categories they belong to
        $vendorProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->get();
        
        // Extract unique category IDs from vendor products
        $categoryIds = collect();
        foreach ($vendorProducts as $product) {
            if ($product->product_categories && is_array($product->product_categories)) {
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id'])) {
                        $categoryIds->push((int)$catData['category_id']);
                    }
                }
            }
        }
        $categoryIds = $categoryIds->unique()->values();
        
        // Get categories that have vendor products
        $categories = Category::whereIn('id', $categoryIds)
            ->where('is_active', true)
            ->withCount(['subCategories' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get()
            ->map(function($category) use ($vendorProducts) {
                // Count products in this category from this vendor
                $productCount = $vendorProducts->filter(function($product) use ($category) {
                    return $this->productBelongsToCategory($product, $category->id);
                })->count();
                
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image_url' => $category->image_url,
                    'subcategories_count' => $category->sub_categories_count,
                    'product_count' => $productCount,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/categories/{id}/subcategories",
     *     summary="Get subcategories",
     *     description="Get subcategories for a specific category that have products from the customer's vendor",
     *     operationId="getCustomerCategorySubcategories",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", description="Category ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function subcategories(Request $request, $categoryId)
    {
        $customer = $this->getCustomer($request);
        
        // Get the category
        $category = Category::where('id', $categoryId)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data' => null
            ], 404);
        }

        // Get vendor products in this category with mainPhoto and variations
        $vendorProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->with(['variations'])
            ->get()
            ->filter(function($product) use ($categoryId) {
                return $this->productBelongsToCategory($product, $categoryId);
            });

        // Extract unique subcategory IDs from vendor products in this category
        $subcategoryIds = collect();
        foreach ($vendorProducts as $product) {
            if ($product->product_categories && is_array($product->product_categories)) {
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id']) && (int)$catData['category_id'] === (int)$categoryId) {
                        // Handle subcategory_ids array
                        if (isset($catData['subcategory_ids'])) {
                            $subIds = $catData['subcategory_ids'];
                            // Handle case where subcategory_ids might be a single value
                            if (!is_array($subIds)) {
                                $subIds = [$subIds];
                            }
                            foreach ($subIds as $subId) {
                                $subcategoryIds->push((int)$subId);
                            }
                        }
                        // Also check legacy 'subcategory_id' field
                        if (isset($catData['subcategory_id'])) {
                            $subcategoryIds->push((int)$catData['subcategory_id']);
                        }
                    }
                }
            }
        }
        $subcategoryIds = $subcategoryIds->unique()->values();

        // Get subcategories that have vendor products
        $subcategories = SubCategory::where('category_id', $categoryId)
            ->whereIn('id', $subcategoryIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($subcategory) use ($vendorProducts, $categoryId, $customer) {
                // Get products in this subcategory from this vendor
                $subcategoryProducts = $vendorProducts->filter(function($product) use ($subcategory, $categoryId) {
                    return $this->productBelongsToSubcategory($product, $subcategory->id, $categoryId);
                });
                
                // Transform products for this subcategory
                $transformedProducts = $subcategoryProducts->values()->map(function($product) use ($customer) {
                    $priceRange = $product->price_range;
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'product_type' => $product->product_type,
                        'mrp' => $product->mrp,
                        'selling_price' => $product->selling_price,
                        'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                        'customer_discount' => $customer->discount_percentage,
                        'price_range' => [
                            'min' => $customer->getDiscountedPrice($priceRange['min']),
                            'max' => $customer->getDiscountedPrice($priceRange['max']),
                        ],
                        'in_stock' => $product->in_stock,
                        'stock_quantity' => $product->stock_quantity,
                        'main_photo_url' => $product->mainPhoto?->url,
                        'has_variations' => $product->isVariable(),
                        'variations_count' => $product->variations->count(),
                    ];
                });
                
                return [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                    'description' => $subcategory->description,
                    'image_url' => $subcategory->image_url,
                    'product_count' => $subcategoryProducts->count(),
                    'products' => $transformedProducts,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Subcategories retrieved successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
                'subcategories' => $subcategories
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/home",
     *     summary="Get customer home page data",
     *     description="Get home page data including featured products and latest products from customer's vendor, and categories",
     *     operationId="getCustomerHome",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function home(Request $request)
    {
        $customer = $this->getCustomer($request);
        $vendor = $customer->vendor;

        // Get featured/latest products from customer's vendor
        $featuredProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->where('in_stock', true)
            ->where('is_featured', true)
            ->with(['variations', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // If no featured products, fallback to latest products from customer's vendor
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::where('vendor_id', $customer->vendor_id)
                ->whereIn('status', ['active', 'published'])
                ->where('in_stock', true)
                ->with(['variations', 'vendor'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        $featuredProducts = $featuredProducts->map(function($product) use ($customer) {
                $priceRange = $product->price_range;
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'product_type' => $product->product_type ?? 'simple',
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                    'customer_discount' => $customer->discount_percentage,
                    'price_range' => [
                        'min' => $customer->getDiscountedPrice($priceRange['min']),
                        'max' => $customer->getDiscountedPrice($priceRange['max']),
                    ],
                    'main_photo_url' => $product->mainPhoto?->url,
                    'in_stock' => $product->in_stock,
                    'stock_quantity' => $product->stock_quantity,
                    'has_variations' => $product->isVariable(),
                    'variations_count' => $product->variations->count(),
                    'vendor_name' => $product->vendor?->store_name,
                    'vendor_id' => $product->vendor_id,
                ];
            });

        // Get latest products from customer's vendor (all products, not just featured)
        $latestProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->where('in_stock', true)
            ->with(['variations', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($product) use ($customer) {
                $priceRange = $product->price_range;
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'product_type' => $product->product_type ?? 'simple',
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                    'customer_discount' => $customer->discount_percentage,
                    'price_range' => [
                        'min' => $customer->getDiscountedPrice($priceRange['min']),
                        'max' => $customer->getDiscountedPrice($priceRange['max']),
                    ],
                    'main_photo_url' => $product->mainPhoto?->url,
                    'in_stock' => $product->in_stock,
                    'stock_quantity' => $product->stock_quantity,
                    'has_variations' => $product->isVariable(),
                    'variations_count' => $product->variations->count(),
                    'vendor_name' => $product->vendor?->store_name,
                    'vendor_id' => $product->vendor_id,
                ];
            });

        // Get all vendor products to find categories
        $vendorProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->get();
        
        // Extract unique category IDs from vendor products
        $categoryIds = collect();
        foreach ($vendorProducts as $product) {
            if ($product->product_categories && is_array($product->product_categories)) {
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id'])) {
                        $categoryIds->push((int)$catData['category_id']);
                    }
                }
            }
        }
        $categoryIds = $categoryIds->unique()->values();

        // Get categories that have vendor products
        $categories = Category::whereIn('id', $categoryIds)
            ->where('is_active', true)
            ->limit(10)
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image_url' => $category->image_url,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Home data retrieved successfully',
            'data' => [
                'vendor' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url, // Banner image URL
                    'banner_redirect_url' => $vendor->banner_redirect_url, // URL to redirect when banner is clicked
                    'store_description' => $vendor->store_description,
                ],
                'customer' => [
                    'name' => $customer->name,
                    'discount_percentage' => $customer->discount_percentage,
                ],
                'featured_products' => $featuredProducts,
                'latest_products' => $latestProducts,
                'categories' => $categories,
                'total_products' => $vendorProducts->count(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/search",
     *     summary="Search products",
     *     description="Search products from the customer's vendor only",
     *     operationId="searchCustomerProducts",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="q", in="query", description="Search query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="limit", in="query", description="Number of results", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function search(Request $request)
    {
        $customer = $this->getCustomer($request);
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 20), 50);

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'message' => 'No search query provided',
                'data' => []
            ]);
        }

        $products = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->with(['variations'])
            ->limit($limit)
            ->get()
            ->map(function($product) use ($customer) {
                $priceRange = $product->price_range;
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'product_type' => $product->product_type ?? 'simple',
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                    'customer_discount' => $customer->discount_percentage,
                    'price_range' => [
                        'min' => $customer->getDiscountedPrice($priceRange['min']),
                        'max' => $customer->getDiscountedPrice($priceRange['max']),
                    ],
                    'main_photo_url' => $product->mainPhoto?->url,
                    'in_stock' => $product->in_stock,
                    'stock_quantity' => $product->stock_quantity,
                    'has_variations' => $product->isVariable(),
                    'variations_count' => $product->variations->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully',
            'data' => $products
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/products/by-category/{categoryId}",
     *     summary="Get products by category",
     *     description="Get products from the customer's vendor filtered by category",
     *     operationId="getCustomerProductsByCategory",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="categoryId", in="path", description="Category ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="subcategory_id", in="query", description="Filter by subcategory", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort by field", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function productsByCategory(Request $request, $categoryId)
    {
        $customer = $this->getCustomer($request);
        
        // Verify category exists
        $category = Category::where('id', $categoryId)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
                'data' => null
            ], 404);
        }

        // Get vendor products
        $vendorProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->with(['variations'])
            ->get();

        // Filter by category
        $filteredProducts = $vendorProducts->filter(function($product) use ($categoryId) {
            return $this->productBelongsToCategory($product, $categoryId);
        });

        // Filter by subcategory if provided
        if ($request->filled('subcategory_id')) {
            $subcategoryId = $request->subcategory_id;
            $filteredProducts = $filteredProducts->filter(function($product) use ($subcategoryId, $categoryId) {
                return $this->productBelongsToSubcategory($product, $subcategoryId, $categoryId);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'name') {
            $filteredProducts = $sortOrder === 'desc' 
                ? $filteredProducts->sortByDesc('name') 
                : $filteredProducts->sortBy('name');
        } elseif ($sortBy === 'selling_price' || $sortBy === 'price') {
            $filteredProducts = $sortOrder === 'desc' 
                ? $filteredProducts->sortByDesc('selling_price') 
                : $filteredProducts->sortBy('selling_price');
        } elseif ($sortBy === 'created_at') {
            $filteredProducts = $sortOrder === 'desc' 
                ? $filteredProducts->sortByDesc('created_at') 
                : $filteredProducts->sortBy('created_at');
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50);
        $page = $request->get('page', 1);
        $total = $filteredProducts->count();
        $paginatedProducts = $filteredProducts->forPage($page, $perPage)->values();

        // Transform products
        $transformedProducts = $paginatedProducts->map(function($product) use ($customer) {
            $priceRange = $product->price_range;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type' => $product->product_type,
                'mrp' => $product->mrp,
                'selling_price' => $product->selling_price,
                'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                'customer_discount' => $customer->discount_percentage,
                'price_range' => [
                    'min' => $customer->getDiscountedPrice($priceRange['min']),
                    'max' => $customer->getDiscountedPrice($priceRange['max']),
                ],
                'in_stock' => $product->in_stock,
                'stock_quantity' => $product->stock_quantity,
                'main_photo_url' => $product->mainPhoto?->url,
                'has_variations' => $product->isVariable(),
                'variations_count' => $product->variations->count(),
            ];
        });

        // Get subcategories for this category that have vendor products
        $subcategoryIds = collect();
        foreach ($filteredProducts as $product) {
            if ($product->product_categories && is_array($product->product_categories)) {
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id']) && (int)$catData['category_id'] === (int)$categoryId) {
                        // Handle subcategory_ids array
                        if (isset($catData['subcategory_ids'])) {
                            $subIds = $catData['subcategory_ids'];
                            // Handle case where subcategory_ids might be a single value
                            if (!is_array($subIds)) {
                                $subIds = [$subIds];
                            }
                            foreach ($subIds as $subId) {
                                $subcategoryIds->push((int)$subId);
                            }
                        }
                        // Also check legacy 'subcategory_id' field
                        if (isset($catData['subcategory_id'])) {
                            $subcategoryIds->push((int)$catData['subcategory_id']);
                        }
                    }
                }
            }
        }
        $subcategoryIds = $subcategoryIds->unique()->values();

        $subcategories = SubCategory::where('category_id', $categoryId)
            ->whereIn('id', $subcategoryIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'subcategories' => $subcategories,
                'products' => [
                    'data' => $transformedProducts,
                    'current_page' => (int) $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
                ],
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customer/products/by-subcategory/{subcategoryId}",
     *     summary="Get products by subcategory",
     *     description="Get products from the customer's vendor filtered by subcategory",
     *     operationId="getCustomerProductsBySubcategory",
     *     tags={"Customer Store"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="subcategoryId", in="path", description="Subcategory ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort by field", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Subcategory not found")
     * )
     */
    public function productsBySubcategory(Request $request, $subcategoryId)
    {
        $customer = $this->getCustomer($request);
        
        // Verify subcategory exists
        $subcategory = SubCategory::where('id', $subcategoryId)
            ->where('is_active', true)
            ->with('category')
            ->first();

        if (!$subcategory) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found',
                'data' => null
            ], 404);
        }

        $categoryId = $subcategory->category_id;

        // Get vendor products
        $vendorProducts = Product::where('vendor_id', $customer->vendor_id)
            ->whereIn('status', ['active', 'published'])
            ->with(['variations'])
            ->get();

        // Filter by subcategory
        $filteredProducts = $vendorProducts->filter(function($product) use ($subcategoryId, $categoryId) {
            return $this->productBelongsToSubcategory($product, $subcategoryId, $categoryId);
        });

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'name') {
            $filteredProducts = $sortOrder === 'desc' 
                ? $filteredProducts->sortByDesc('name') 
                : $filteredProducts->sortBy('name');
        } elseif ($sortBy === 'selling_price' || $sortBy === 'price') {
            $filteredProducts = $sortOrder === 'desc' 
                ? $filteredProducts->sortByDesc('selling_price') 
                : $filteredProducts->sortBy('selling_price');
        } elseif ($sortBy === 'created_at') {
            $filteredProducts = $sortOrder === 'desc' 
                ? $filteredProducts->sortByDesc('created_at') 
                : $filteredProducts->sortBy('created_at');
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 50);
        $page = $request->get('page', 1);
        $total = $filteredProducts->count();
        $paginatedProducts = $filteredProducts->forPage($page, $perPage)->values();

        // Transform products
        $transformedProducts = $paginatedProducts->map(function($product) use ($customer) {
            $priceRange = $product->price_range;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type' => $product->product_type,
                'mrp' => $product->mrp,
                'selling_price' => $product->selling_price,
                'discounted_price' => $customer->getDiscountedPrice($product->selling_price ?? $product->mrp),
                'customer_discount' => $customer->discount_percentage,
                'price_range' => [
                    'min' => $customer->getDiscountedPrice($priceRange['min']),
                    'max' => $customer->getDiscountedPrice($priceRange['max']),
                ],
                'in_stock' => $product->in_stock,
                'stock_quantity' => $product->stock_quantity,
                'main_photo_url' => $product->mainPhoto?->url,
                'has_variations' => $product->isVariable(),
                'variations_count' => $product->variations->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                ],
                'category' => [
                    'id' => $subcategory->category->id,
                    'name' => $subcategory->category->name,
                    'slug' => $subcategory->category->slug,
                ],
                'products' => [
                    'data' => $transformedProducts,
                    'current_page' => (int) $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
                ],
            ]
        ]);
    }
}
