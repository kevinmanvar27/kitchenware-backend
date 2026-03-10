<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Product Search",
 *     description="API Endpoints for Product Search and Filtering"
 * )
 */
class ProductSearchController extends ApiController
{
    /**
     * Search products by name or description
     * 
     * @OA\Get(
     *      path="/api/v1/products/search",
     *      operationId="searchProducts",
     *      tags={"Product Search"},
     *      summary="Search products",
     *      description="Search products by name or description",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="q",
     *          description="Search query (optional - returns all products if not provided)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page (default: 15, max: 50)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="sort_by",
     *          description="Sort field (name, mrp, created_at)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="sort_order",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
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
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
            'sort_by' => 'nullable|string|in:name,mrp,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
        ]);

        $query = $request->q;
        $perPage = $request->per_page ?? 15;
        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';

        $productsQuery = Product::where('status', 'published')
            ->with([]);

        // Only apply search filter if query is provided
        if ($query && strlen(trim($query)) > 0) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        $products = $productsQuery->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        // Add discounted price for each product
        $user = $request->user();
        $products->getCollection()->transform(function ($product) use ($user) {
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                ? $product->selling_price 
                : $product->mrp;
            
            $product->discounted_price = function_exists('calculateDiscountedPrice') 
                ? calculateDiscountedPrice($priceToUse, $user) 
                : $priceToUse;
            
            // Ensure product_type is set (default to 'simple' if not set)
            $product->product_type = $product->product_type ?? 'simple';
            
            return $product;
        });

        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    /**
     * Get products by category
     * 
     * @OA\Get(
     *      path="/api/v1/products/by-category/{categoryId}",
     *      operationId="getProductsByCategory",
     *      tags={"Product Search"},
     *      summary="Get products by category",
     *      description="Get all products in a specific category (uses JSON product_categories field)",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="categoryId",
     *          description="Category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="subcategory",
     *          description="Subcategory id to filter by (optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page (default: 15, max: 50)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="sort_by",
     *          description="Sort field (name, selling_price, created_at)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="sort_order",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
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
     *          response=404,
     *          description="Category not found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function byCategory(Request $request, $categoryId)
    {
        $category = Category::with(['subCategories' => function ($query) {
            $query->where('is_active', true);
        }])->find($categoryId);

        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        // Check if category is active
        if (!$category->is_active) {
            return $this->sendError('Category is not active.', [], 404);
        }

        $perPage = $request->per_page ?? 15;
        $perPage = min($perPage, 50);
        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';
        $selectedSubcategoryId = $request->query('subcategory');

        // Get products using JSON filtering (same as web flow)
        $products = Product::where('status', 'published')
            ->with()
            ->get()
            ->filter(function ($product) use ($categoryId, $selectedSubcategoryId) {
                if (!$product->product_categories) {
                    return false;
                }
                
                // Check if product belongs to the main category
                $belongsToCategory = false;
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id']) && $catData['category_id'] == $categoryId) {
                        $belongsToCategory = true;
                        break;
                    }
                }
                
                if (!$belongsToCategory) {
                    return false;
                }
                
                // If a subcategory filter is applied, check if product belongs to that subcategory
                if ($selectedSubcategoryId) {
                    foreach ($product->product_categories as $catData) {
                        // Check if subcategory_ids array exists and contains the selected subcategory
                        if (isset($catData['subcategory_ids']) && in_array($selectedSubcategoryId, $catData['subcategory_ids'])) {
                            return true;
                        }
                    }
                    return false;
                }
                
                return true;
            })
            ->values();

        // Sort products
        if ($sortBy === 'name') {
            $products = $sortOrder === 'desc' ? $products->sortByDesc('name') : $products->sortBy('name');
        } elseif ($sortBy === 'selling_price' || $sortBy === 'price-low') {
            $products = $products->sortBy('selling_price');
        } elseif ($sortBy === 'price-high') {
            $products = $products->sortByDesc('selling_price');
        } elseif ($sortBy === 'created_at') {
            $products = $sortOrder === 'desc' ? $products->sortByDesc('created_at') : $products->sortBy('created_at');
        }

        // Add discounted price for each product
        $user = $request->user();
        $products = $products->map(function ($product) use ($user) {
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                ? $product->selling_price 
                : $product->mrp;
            
            $product->discounted_price = function_exists('calculateDiscountedPrice') 
                ? calculateDiscountedPrice($priceToUse, $user) 
                : $priceToUse;
            
            // Ensure product_type is set (default to 'simple' if not set)
            $product->product_type = $product->product_type ?? 'simple';
            
            return $product;
        });

        // Manual pagination
        $page = $request->page ?? 1;
        $total = $products->count();
        $paginatedProducts = $products->forPage($page, $perPage)->values();

        // Filter subcategories to only show those with products (same as web flow)
        $subCategories = $category->subCategories->filter(function ($subCategory) use ($categoryId) {
            $products = Product::where('status', 'published')
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
                });
            
            return $products->count() > 0;
        })->values();

        return $this->sendResponse([
            'category' => $category,
            'sub_categories' => $subCategories,
            'products' => [
                'data' => $paginatedProducts,
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ], 'Products retrieved successfully.');
    }

    /**
     * Get products by subcategory
     * 
     * @OA\Get(
     *      path="/api/v1/products/by-subcategory/{subcategoryId}",
     *      operationId="getProductsBySubcategory",
     *      tags={"Product Search"},
     *      summary="Get products by subcategory",
     *      description="Get all products in a specific subcategory (uses JSON product_categories field)",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="subcategoryId",
     *          description="Subcategory id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page (default: 15, max: 50)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="sort_by",
     *          description="Sort field (name, selling_price, created_at)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="sort_order",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
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
     *          response=404,
     *          description="Subcategory not found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $subcategoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function bySubcategory(Request $request, $subcategoryId)
    {
        $subcategory = SubCategory::with(['category'])->find($subcategoryId);

        if (!$subcategory) {
            return $this->sendError('Subcategory not found.', [], 404);
        }

        // Check if subcategory is active
        if (!$subcategory->is_active) {
            return $this->sendError('Subcategory is not active.', [], 404);
        }

        $perPage = $request->per_page ?? 15;
        $perPage = min($perPage, 50);
        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';

        $categoryId = $subcategory->category_id;

        // Get products using JSON filtering (same as web flow)
        $products = Product::where('status', 'published')
            ->with()
            ->get()
            ->filter(function ($product) use ($categoryId, $subcategoryId) {
                if (!$product->product_categories) {
                    return false;
                }
                
                // Check if product belongs to both the main category and this specific subcategory
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id']) && $catData['category_id'] == $categoryId &&
                        isset($catData['subcategory_ids']) && in_array($subcategoryId, $catData['subcategory_ids'])) {
                        return true;
                    }
                }
                
                return false;
            })
            ->values();

        // Sort products
        if ($sortBy === 'name') {
            $products = $sortOrder === 'desc' ? $products->sortByDesc('name') : $products->sortBy('name');
        } elseif ($sortBy === 'selling_price' || $sortBy === 'price-low') {
            $products = $products->sortBy('selling_price');
        } elseif ($sortBy === 'price-high') {
            $products = $products->sortByDesc('selling_price');
        } elseif ($sortBy === 'created_at') {
            $products = $sortOrder === 'desc' ? $products->sortByDesc('created_at') : $products->sortBy('created_at');
        }

        // Add discounted price for each product
        $user = $request->user();
        $products = $products->map(function ($product) use ($user) {
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                ? $product->selling_price 
                : $product->mrp;
            
            $product->discounted_price = function_exists('calculateDiscountedPrice') 
                ? calculateDiscountedPrice($priceToUse, $user) 
                : $priceToUse;
            
            // Ensure product_type is set (default to 'simple' if not set)
            $product->product_type = $product->product_type ?? 'simple';
            
            return $product;
        });

        // Manual pagination
        $page = $request->page ?? 1;
        $total = $products->count();
        $paginatedProducts = $products->forPage($page, $perPage)->values();

        return $this->sendResponse([
            'subcategory' => $subcategory,
            'category' => $subcategory->category,
            'products' => [
                'data' => $paginatedProducts,
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ], 'Products retrieved successfully.');
    }

    /**
     * Get subcategories by category
     * 
     * @OA\Get(
     *      path="/api/v1/categories/{id}/subcategories",
     *      operationId="getSubcategoriesByCategory",
     *      tags={"Product Search"},
     *      summary="Get subcategories by category",
     *      description="Get all active subcategories in a specific category that have products",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Category id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
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
     *          response=404,
     *          description="Category not found"
     *      )
     * )
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function subcategoriesByCategory($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->sendError('Category not found.', [], 404);
        }

        // Get active subcategories with images (same as web flow)
        $subcategories = SubCategory::where('category_id', $id)
            ->where('is_active', true)
            ->with('image')
            ->orderBy('name')
            ->get()
            ->filter(function ($subCategory) use ($id) {
                // Check if this subcategory has any products in this category (same as web flow)
                $products = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($id, $subCategory) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        // Check if product belongs to both the main category and this specific subcategory
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $id) {
                                // Handle subcategory_ids - could be array or single value
                                if (isset($catData['subcategory_ids'])) {
                                    $subIds = is_array($catData['subcategory_ids']) 
                                        ? $catData['subcategory_ids'] 
                                        : [$catData['subcategory_ids']];
                                    if (in_array($subCategory->id, $subIds)) {
                                        return true;
                                    }
                                }
                                // Also check legacy 'subcategory_id' field
                                if (isset($catData['subcategory_id']) && $catData['subcategory_id'] == $subCategory->id) {
                                    return true;
                                }
                            }
                        }
                        
                        return false;
                    });
                
                return $products->count() > 0;
            })
            ->values()
            ->map(function ($subCategory) use ($id) {
                // Add product count to each subcategory
                $productCount = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($id, $subCategory) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $id) {
                                // Handle subcategory_ids - could be array or single value
                                if (isset($catData['subcategory_ids'])) {
                                    $subIds = is_array($catData['subcategory_ids']) 
                                        ? $catData['subcategory_ids'] 
                                        : [$catData['subcategory_ids']];
                                    if (in_array($subCategory->id, $subIds)) {
                                        return true;
                                    }
                                }
                                // Also check legacy 'subcategory_id' field
                                if (isset($catData['subcategory_id']) && $catData['subcategory_id'] == $subCategory->id) {
                                    return true;
                                }
                            }
                        }
                        
                        return false;
                    })
                    ->count();
                
                $subCategory->product_count = $productCount;
                return $subCategory;
            });

        return $this->sendResponse([
            'category' => $category,
            'subcategories' => $subcategories,
        ], 'Subcategories retrieved successfully.');
    }
}
