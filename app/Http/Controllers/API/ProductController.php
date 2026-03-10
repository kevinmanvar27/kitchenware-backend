<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Wishlist;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints for Product Management"
 * )
 */
class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/products",
     *      operationId="getProductsList",
     *      tags={"Products"},
     *      summary="Get list of products",
     *      description="Returns list of products with pagination and images. Supports filtering by category and subcategory.",
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page (default: 15, max: 50)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category_id",
     *          description="Filter by category ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="subcategory_id",
     *          description="Filter by subcategory ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort_by",
     *          description="Sort field (name, mrp, selling_price, created_at)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort_order",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Products retrieved successfully."),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="products",
     *                      type="object",
     *                      @OA\Property(
     *                          property="data",
     *                          type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="id", type="integer", example=1),
     *                              @OA\Property(property="name", type="string", example="iPhone 13"),
     *                              @OA\Property(property="slug", type="string", example="iphone-13"),
     *                              @OA\Property(property="mrp", type="number", format="float", example=999.99),
     *                              @OA\Property(property="selling_price", type="number", format="float", example=899.99),
     *                              @OA\Property(property="discounted_price", type="number", format="float", example=879.99),
     *                              @OA\Property(property="in_stock", type="boolean", example=true),
     *                              @OA\Property(property="main_image_url", type="string", example="https://example.com/storage/images/iphone13.jpg"),
     *                              @OA\Property(
     *                                  property="mainPhoto",
     *                                  type="object",
     *                                  @OA\Property(property="id", type="integer", example=3),
     *                                  @OA\Property(property="url", type="string", example="https://example.com/storage/images/iphone13.jpg")
     *                              )
     *                          )
     *                      ),
     *                      @OA\Property(property="current_page", type="integer", example=1),
     *                      @OA\Property(property="per_page", type="integer", example=15),
     *                      @OA\Property(property="total", type="integer", example=50),
     *                      @OA\Property(property="last_page", type="integer", example=4)
     *                  ),
     *                  @OA\Property(
     *                      property="category",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Electronics"),
     *                      @OA\Property(property="image_url", type="string", example="https://example.com/storage/images/electronics.jpg"),
     *                      @OA\Property(
     *                          property="image",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="url", type="string", example="https://example.com/storage/images/electronics.jpg")
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="subcategory",
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="name", type="string", example="Smartphones"),
     *                      @OA\Property(property="image_url", type="string", example="https://example.com/storage/images/smartphones.jpg"),
     *                      @OA\Property(
     *                          property="image",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=2),
     *                          @OA\Property(property="url", type="string", example="https://example.com/storage/images/smartphones.jpg")
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="subcategories",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer", example=2),
     *                          @OA\Property(property="name", type="string", example="Smartphones"),
     *                          @OA\Property(property="product_count", type="integer", example=15),
     *                          @OA\Property(property="image_url", type="string", example="https://example.com/storage/images/smartphones.jpg"),
     *                          @OA\Property(
     *                              property="image",
     *                              type="object",
     *                              @OA\Property(property="id", type="integer", example=2),
     *                              @OA\Property(property="url", type="string", example="https://example.com/storage/images/smartphones.jpg")
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:sub_categories,id',
            'per_page' => 'nullable|integer|min:1|max:50',
            'sort_by' => 'nullable|string|in:name,mrp,selling_price,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $categoryId = $request->category_id;
        $subcategoryId = $request->subcategory_id;
        $perPage = min($request->per_page ?? 15, 50);
        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';
        $user = $request->user();

        // Get category and subcategory info if filtering
        $categoryInfo = null;
        $subcategoryInfo = null;
        $availableSubcategories = collect();

        if ($categoryId) {
            $categoryInfo = Category::find($categoryId);
            if (!$categoryInfo || !$categoryInfo->is_active) {
                return $this->sendError('Category not found or inactive.', [], 404);
            }
        }

        if ($subcategoryId) {
            $subcategoryInfo = SubCategory::with(['category'])->find($subcategoryId);
            if (!$subcategoryInfo || !$subcategoryInfo->is_active) {
                return $this->sendError('Subcategory not found or inactive.', [], 404);
            }
            
            // If subcategory is provided but category is not, use the subcategory's category
            if (!$categoryId) {
                $categoryId = $subcategoryInfo->category_id;
                $categoryInfo = $subcategoryInfo->category;
            }
        }

        // If filtering by category or subcategory, use JSON filtering (same as web flow)
        if ($categoryId || $subcategoryId) {
            $products = Product::where('status', 'published')
                ->get()
                ->filter(function ($product) use ($categoryId, $subcategoryId) {
                    if (!$product->product_categories) {
                        return false;
                    }
                    
                    foreach ($product->product_categories as $catData) {
                        // Check category match
                        if ($categoryId && (!isset($catData['category_id']) || $catData['category_id'] != $categoryId)) {
                            continue;
                        }
                        
                        // If subcategory filter is applied
                        if ($subcategoryId) {
                            if (isset($catData['subcategory_ids']) && in_array($subcategoryId, $catData['subcategory_ids'])) {
                                return true;
                            }
                        } else {
                            // No subcategory filter, just match category
                            if (isset($catData['category_id']) && $catData['category_id'] == $categoryId) {
                                return true;
                            }
                        }
                    }
                    
                    return false;
                })
                ->values();

            // Sort products
            if ($sortBy === 'name') {
                $products = $sortOrder === 'desc' ? $products->sortByDesc('name') : $products->sortBy('name');
            } elseif ($sortBy === 'mrp') {
                $products = $sortOrder === 'desc' ? $products->sortByDesc('mrp') : $products->sortBy('mrp');
            } elseif ($sortBy === 'selling_price') {
                $products = $sortOrder === 'desc' ? $products->sortByDesc('selling_price') : $products->sortBy('selling_price');
            } elseif ($sortBy === 'created_at') {
                $products = $sortOrder === 'desc' ? $products->sortByDesc('created_at') : $products->sortBy('created_at');
            }

            // Add discounted price for each product
            $products = $products->map(function ($product) use ($user) {
                $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                    ? $product->selling_price 
                    : $product->mrp;
                
                $product->discounted_price = function_exists('calculateDiscountedPrice') 
                    ? calculateDiscountedPrice($priceToUse, $user) 
                    : $priceToUse;
                
                // Ensure product_type is set (default to 'simple' if not set)
                $product->product_type = $product->product_type ?? 'simple';
                
                // Add main image URL
                $product->main_image_url = $product->main_photo_url;
                
                return $product;
            });

            // Manual pagination
            $page = $request->page ?? 1;
            $total = $products->count();
            $paginatedProducts = $products->forPage($page, $perPage)->values();

            // Get available subcategories for the category (with product counts)
            if ($categoryId && !$subcategoryId) {
                $availableSubcategories = SubCategory::where('category_id', $categoryId)
                    ->where('is_active', true)
                    ->get()
                    ->filter(function ($subCategory) use ($categoryId) {
                        $productCount = Product::where('status', 'published')
                            ->get()
                            ->filter(function ($product) use ($categoryId, $subCategory) {
                                if (!$product->product_categories) {
                                    return false;
                                }
                                
                                foreach ($product->product_categories as $catData) {
                                    if (isset($catData['category_id']) && $catData['category_id'] == $categoryId &&
                                        isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                        return true;
                                    }
                                }
                                
                                return false;
                            })
                            ->count();
                        
                        return $productCount > 0;
                    })
                    ->map(function ($subCategory) use ($categoryId) {
                        $productCount = Product::where('status', 'published')
                            ->get()
                            ->filter(function ($product) use ($categoryId, $subCategory) {
                                if (!$product->product_categories) {
                                    return false;
                                }
                                
                                foreach ($product->product_categories as $catData) {
                                    if (isset($catData['category_id']) && $catData['category_id'] == $categoryId &&
                                        isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                        return true;
                                    }
                                }
                                
                                return false;
                            })
                            ->count();
                        
                        $subCategory->product_count = $productCount;
                        
                        return $subCategory;
                    })
                    ->values();
            }

            // Build response with category/subcategory info
            $response = [
                'products' => [
                    'data' => $paginatedProducts,
                    'current_page' => (int) $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $total > 0 ? ceil($total / $perPage) : 1,
                ],
            ];

            if ($categoryInfo) {
                $response['category'] = $categoryInfo;
            }

            if ($subcategoryInfo) {
                $response['subcategory'] = $subcategoryInfo;
            }

            if ($availableSubcategories->isNotEmpty()) {
                $response['subcategories'] = $availableSubcategories;
            }

            return $this->sendResponse($response, 'Products retrieved successfully.');
        }

        // No category/subcategory filter - return all published products with standard pagination
        $productsQuery = Product::where('status', 'published')
            ->orderBy($sortBy, $sortOrder);

        $products = $productsQuery->paginate($perPage);

        // Add discounted price for each product
        $products->getCollection()->transform(function ($product) use ($user) {
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                ? $product->selling_price 
                : $product->mrp;
            
            $product->discounted_price = function_exists('calculateDiscountedPrice') 
                ? calculateDiscountedPrice($priceToUse, $user) 
                : $priceToUse;
            
            // Ensure product_type is set (default to 'simple' if not set)
            $product->product_type = $product->product_type ?? 'simple';
            
            // Add main image URL
            $product->main_image_url = $product->main_photo_url;
            
            return $product;
        });

        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/products",
     *      operationId="storeProduct",
     *      tags={"Products"},
     *      summary="Store new product",
     *      description="Returns product data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","mrp","in_stock","status"},
     *              @OA\Property(property="name", type="string", example="Smartphone"),
     *              @OA\Property(property="description", type="string", example="Latest smartphone model"),
     *              @OA\Property(property="mrp", type="number", format="float", example=599.99),
     *              @OA\Property(property="selling_price", type="number", format="float", example=499.99),
     *              @OA\Property(property="in_stock", type="boolean", example=true),
     *              @OA\Property(property="stock_quantity", type="integer", example=100),
     *              @OA\Property(property="status", type="string", example="published"),
     *              @OA\Property(property="main_photo_id", type="integer", example=1),
     *              @OA\Property(property="product_gallery", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="product_categories", type="array", @OA\Items(type="object")),
     *              @OA\Property(property="meta_title", type="string", example="Smartphone"),
     *              @OA\Property(property="meta_description", type="string", example="Latest smartphone model"),
     *              @OA\Property(property="meta_keywords", type="string", example="smartphone, electronics"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mrp' => 'required|numeric|min:0.01',
            'selling_price' => 'nullable|numeric|lt:mrp',
            'in_stock' => 'required|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_gallery' => 'nullable|array',
            'product_categories' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $product = Product::create($request->all());

        return $this->sendResponse($product, 'Product created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/products/{id}",
     *      operationId="getProductById",
     *      tags={"Products"},
     *      summary="Get product information",
     *      description="Returns detailed product data including main photo, gallery photos, related products, wishlist status, and user-specific pricing",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Product retrieved successfully."),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Sample Product"),
     *                  @OA\Property(property="slug", type="string", example="sample-product"),
     *                  @OA\Property(property="description", type="string", example="Product description"),
     *                  @OA\Property(property="mrp", type="number", format="float", example=100.00),
     *                  @OA\Property(property="selling_price", type="number", format="float", example=90.00),
     *                  @OA\Property(property="discounted_price", type="number", format="float", example=85.00, description="User-specific discounted price"),
     *                  @OA\Property(property="in_stock", type="boolean", example=true),
     *                  @OA\Property(property="stock_quantity", type="integer", example=50),
     *                  @OA\Property(property="status", type="string", example="published"),
     *                  @OA\Property(property="product_categories", type="array", @OA\Items(type="integer"), example={1, 2}),
     *                  @OA\Property(property="main_image_url", type="string", example="https://example.com/storage/images/product.jpg"),
     *                  @OA\Property(
     *                      property="main_photo",
     *                      type="object",
     *                      nullable=true,
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="url", type="string", example="https://example.com/photo.jpg")
     *                  ),
     *                  @OA\Property(
     *                      property="gallery_photos",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=2),
     *                          @OA\Property(property="url", type="string", example="https://example.com/gallery1.jpg")
     *                      )
     *                  ),
     *                  @OA\Property(property="is_in_wishlist", type="boolean", example=false),
     *                  @OA\Property(
     *                      property="stock_status",
     *                      type="object",
     *                      @OA\Property(property="available", type="boolean", example=true),
     *                      @OA\Property(property="quantity", type="integer", example=50),
     *                      @OA\Property(property="label", type="string", example="In Stock")
     *                  ),
     *                  @OA\Property(
     *                      property="related_products",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=2),
     *                          @OA\Property(property="name", type="string", example="Related Product"),
     *                          @OA\Property(property="slug", type="string", example="related-product"),
     *                          @OA\Property(property="mrp", type="number", format="float", example=80.00),
     *                          @OA\Property(property="selling_price", type="number", format="float", example=75.00),
     *                          @OA\Property(property="discounted_price", type="number", format="float", example=70.00),
     *                          @OA\Property(property="in_stock", type="boolean", example=true),
     *                          @OA\Property(property="main_image_url", type="string", example="https://example.com/storage/images/related.jpg"),
     *                          @OA\Property(
     *                              property="main_photo",
     *                              type="object",
     *                              nullable=true,
     *                              @OA\Property(property="id", type="integer", example=3),
     *                              @OA\Property(property="url", type="string", example="https://example.com/related.jpg")
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Product not found.")
     *          )
     *      )
     * )
     */
    public function show($id)
    {
        $product = Product::with(['variations' => function($query) {
            $query->orderBy('is_default', 'desc');
        }])->find($id);

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        // Get authenticated user (may be null for public access)
        $user = auth('sanctum')->user();

        // Convert product to array for modification
        $productData = $product->toArray();

        // Add gallery photos from JSON
        $gallery = json_decode($product->gallery, true) ?? [];
        $productData['gallery_photos'] = array_map(function ($path) {
            return [
                'url' => asset('storage/' . $path),
            ];
        }, $gallery);

        // Add main photo URL directly
        $productData['main_image_url'] = $product->main_photo_url;

        // Add user-specific discounted price
        $basePrice = $product->selling_price ?? $product->mrp;
        $productData['discounted_price'] = $user 
            ? calculateDiscountedPrice($basePrice, $user) 
            : $basePrice;

        // Add wishlist status (only if authenticated)
        $productData['is_in_wishlist'] = $user 
            ? Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->exists() 
            : false;

        // Add stock status
        $productData['stock_status'] = [
            'available' => $product->in_stock && ($product->stock_quantity === null || $product->stock_quantity > 0),
            'quantity' => $product->stock_quantity,
            'label' => $this->getStockLabel($product),
        ];

        // Add product variations with images (for variable products)
        if ($product->isVariable() && $product->variations->isNotEmpty()) {
            $productData['variations'] = $product->variations->map(function ($variation) use ($user) {
                $variationData = $variation->toArray();
                
                // Add user-specific discounted price for variation
                $variationBasePrice = $variation->selling_price ?? $variation->mrp;
                $variationData['discounted_price'] = $user 
                    ? calculateDiscountedPrice($variationBasePrice, $user) 
                    : $variationBasePrice;
                
                // Add stock status for variation
                $variationData['stock_status'] = [
                    'available' => $variation->in_stock && ($variation->stock_quantity === null || $variation->stock_quantity > 0),
                    'quantity' => $variation->stock_quantity,
                    'label' => $this->getVariationStockLabel($variation),
                ];
                
                return $variationData;
            })->toArray();
        } else {
            $productData['variations'] = [];
        }

        // Get related products from same category
        $productData['related_products'] = $this->getRelatedProducts($product, $user);

        return $this->sendResponse($productData, 'Product retrieved successfully.');
    }

    /**
     * Get stock status label for a product.
     *
     * @param Product $product
     * @return string
     */
    private function getStockLabel(Product $product): string
    {
        if (!$product->in_stock) {
            return 'Out of Stock';
        }

        if ($product->stock_quantity === null) {
            return 'In Stock';
        }

        if ($product->stock_quantity <= 0) {
            return 'Out of Stock';
        }

        if ($product->stock_quantity <= 5) {
            return 'Low Stock - Only ' . $product->stock_quantity . ' left';
        }

        return 'In Stock';
    }

    /**
     * Get stock label for a product variation.
     *
     * @param ProductVariation $variation
     * @return string
     */
    private function getVariationStockLabel(ProductVariation $variation): string
    {
        if (!$variation->is_active) {
            return 'Unavailable';
        }

        if ($variation->stock_quantity <= 0) {
            return 'Out of Stock';
        }

        if ($variation->stock_quantity <= 5) {
            return 'Low Stock - Only ' . $variation->stock_quantity . ' left';
        }

        return 'In Stock';
    }

    /**
     * Get related products from the same category.
     *
     * @param Product $product
     * @param User|null $user
     * @return array
     */
    private function getRelatedProducts(Product $product, $user = null): array
    {
        // Get product categories
        $categories = $product->product_categories ?? [];

        if (empty($categories)) {
            return [];
        }

        // Find products in the same categories, excluding current product
        $relatedProducts = Product::where('id', '!=', $product->id)
            ->where('status', 'published')
            ->where('in_stock', true)
            ->where(function ($query) use ($categories) {
                foreach ($categories as $categoryId) {
                    $query->orWhereJsonContains('product_categories', $categoryId);
                }
            })
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        // Add discounted prices to related products
        return $relatedProducts->map(function ($relatedProduct) use ($user) {
            $data = [
                'id' => $relatedProduct->id,
                'name' => $relatedProduct->name,
                'slug' => $relatedProduct->slug,
                'mrp' => $relatedProduct->mrp,
                'selling_price' => $relatedProduct->selling_price,
                'in_stock' => $relatedProduct->in_stock,
                'main_image_url' => $relatedProduct->main_photo_url,
            ];

            // Add user-specific discounted price
            $basePrice = $relatedProduct->selling_price ?? $relatedProduct->mrp;
            $data['discounted_price'] = $user 
                ? calculateDiscountedPrice($basePrice, $user) 
                : $basePrice;

            return $data;
        })->toArray();
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/products/{id}",
     *      operationId="updateProduct",
     *      tags={"Products"},
     *      summary="Update existing product",
     *      description="Returns updated product data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","mrp","in_stock","status"},
     *              @OA\Property(property="name", type="string", example="Smartphone"),
     *              @OA\Property(property="description", type="string", example="Latest smartphone model"),
     *              @OA\Property(property="mrp", type="number", format="float", example=599.99),
     *              @OA\Property(property="selling_price", type="number", format="float", example=499.99),
     *              @OA\Property(property="in_stock", type="boolean", example=true),
     *              @OA\Property(property="stock_quantity", type="integer", example=100),
     *              @OA\Property(property="status", type="string", example="published"),
     *              @OA\Property(property="main_photo_id", type="integer", example=1),
     *              @OA\Property(property="product_gallery", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="product_categories", type="array", @OA\Items(type="object")),
     *              @OA\Property(property="meta_title", type="string", example="Smartphone"),
     *              @OA\Property(property="meta_description", type="string", example="Latest smartphone model"),
     *              @OA\Property(property="meta_keywords", type="string", example="smartphone, electronics"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'mrp' => 'required|numeric|min:0.01',
            'selling_price' => 'nullable|numeric|lt:mrp',
            'in_stock' => 'required|boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'main_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_gallery' => 'nullable|array',
            'product_categories' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $product->update($request->all());

        return $this->sendResponse($product, 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/products/{id}",
     *      operationId="deleteProduct",
     *      tags={"Products"},
     *      summary="Delete product",
     *      description="Deletes a product",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Product id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }

        $product->delete();

        return $this->sendResponse(null, 'Product deleted successfully.');
    }
    
    /**
     * Fetch products by IDs
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByIds(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id'
        ]);
        
        // Note: mainPhoto, category, and subCategory are accessors (not relationships)
        // They are computed when accessed, so no eager loading needed
        $products = Product::whereIn('id', $request->product_ids)
            ->where('status', 'active')
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'stock_quantity' => $product->isVariable() ? $product->total_stock : $product->stock_quantity,
                    'main_photo_url' => $product->mainPhoto ? $product->mainPhoto->url : null,
                    'category' => $product->category ? $product->category->name : null,
                    'sub_category' => optional($product->subCategories->first())->name,
                ];
            });
        
        return $this->sendResponse($products, 'Products retrieved successfully.');
    }
}