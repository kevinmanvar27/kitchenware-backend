<?php

namespace App\Http\Controllers\API;

use App\Models\Wishlist;
use App\Models\Product;
use App\Models\ShoppingCartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;


/**
 * @OA\Tag(
 *     name="Wishlist",
 *     description="API Endpoints for User Wishlist Management (Vendor-Aware)"
 * )
 */
class WishlistController extends ApiController
{
    /**
     * Get user's wishlist
     * 
     * Supports both regular users and vendor customers.
     * Automatically detects the user type and returns appropriate wishlist.
     * 
     * @OA\Get(
     *      path="/api/v1/wishlist",
     *      operationId="getWishlist",
     *      tags={"Wishlist"},
     *      summary="Get user's wishlist",
     *      description="Returns the authenticated user's wishlist with product details. Supports vendor-specific wishlists.",
     *      security={{"sanctum": {}}},
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
     *          name="vendor_id",
     *          description="Filter by specific vendor (optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="total", type="integer", example=10),
     *                  @OA\Property(property="grouped_by_vendor", type="object", description="Wishlist items grouped by vendor"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Wishlist retrieved successfully.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $perPage = min($request->per_page ?? 15, 50);
            $vendorId = $request->vendor_id;
            
            // Determine if this is a vendor customer or regular user
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            
            // Build query based on user type
            $query = Wishlist::query();
            
            // Check if vendor_customer_id column exists
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $query->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $query->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                // Fallback for old database structure
                $query->where('user_id', $user->id);
            }
            
            // Filter by vendor if specified and column exists
            if ($vendorId && is_numeric($vendorId) && Schema::hasColumn('wishlists', 'vendor_id')) {
                $query->where('vendor_id', $vendorId);
            }
            
            $wishlistItems = $query
                ->with(['product.vendor', 'vendor'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            // Add discounted price and stock info to each product
            $wishlistItems->getCollection()->transform(function ($item) use ($user, $isVendorCustomer) {
                if ($item->product) {
                    $priceToUse = (!is_null($item->product->selling_price) && $item->product->selling_price !== '' && $item->product->selling_price >= 0) 
                        ? $item->product->selling_price 
                        : $item->product->mrp;
                    
                    // Calculate discounted price based on user type
                    if ($isVendorCustomer && method_exists($user, 'getDiscountedPrice')) {
                        $item->product->discounted_price = $user->getDiscountedPrice($priceToUse);
                    } else {
                        $item->product->discounted_price = function_exists('calculateDiscountedPrice') 
                            ? calculateDiscountedPrice($priceToUse, $user) 
                            : $priceToUse;
                    }
                    
                    $item->product->is_available = $item->product->in_stock && 
                        in_array($item->product->status, ['active', 'published']);
                    
                    // Add vendor information
                    $item->vendor_info = $item->vendor ? [
                        'id' => $item->vendor->id,
                        'store_name' => $item->vendor->store_name,
                        'store_slug' => $item->vendor->store_slug,
                    ] : null;
                }
                
                return $item;
            });
            
            // Filter out items where product no longer exists
            $validItems = $wishlistItems->getCollection()->filter(function ($item) {
                return $item->product !== null;
            })->values();
            
            $wishlistItems->setCollection($validItems);
            
            // Group items by vendor for better organization
            $groupedByVendor = $validItems->groupBy('vendor_id')->map(function ($items, $vendorId) {
                $vendor = $items->first()->vendor;
                return [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor ? $vendor->store_name : 'Main Store',
                    'vendor_slug' => $vendor ? $vendor->store_slug : null,
                    'items' => $items,
                    'count' => $items->count(),
                ];
            })->values();
            
            // Get total count
            $totalQuery = Wishlist::query();
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $totalQuery->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $totalQuery->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $totalQuery->where('user_id', $user->id);
            }
            
            return $this->sendResponse([
                'items' => $wishlistItems,
                'total' => $totalQuery->count(),
                'grouped_by_vendor' => $groupedByVendor,
                'user_type' => $isVendorCustomer ? 'vendor_customer' : 'regular_user',
            ], 'Wishlist retrieved successfully.');
            
        } catch (\Exception $e) {
            \Log::error('Wishlist Index Error: ' . $e->getMessage(), [
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while retrieving wishlist.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Add product to wishlist
     * 
     * Supports both regular users and vendor customers.
     * Automatically tracks the vendor for vendor-specific wishlists.
     * 
     * @OA\Post(
     *      path="/api/v1/wishlist/{productId}",
     *      operationId="addToWishlist",
     *      tags={"Wishlist"},
     *      summary="Add product to wishlist",
     *      description="Add a product to the authenticated user's wishlist. Supports vendor-specific wishlists.",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to add",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Product added to wishlist",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="Product added to wishlist.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Product already in wishlist"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, $productId)
    {
        try {
            // Validate product ID
            if (!is_numeric($productId) || $productId <= 0) {
                return $this->sendError('Invalid product ID.', [], 400);
            }

            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            
            // Check if product exists and is published
            $product = Product::where('id', $productId)
                ->whereIn('status', ['published', 'active'])
                ->first();
            
            if (!$product) {
                return $this->sendError('Product not found or not available.', [], 404);
            }
            
            // Check if vendor columns exist
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            $hasVendorIdColumn = Schema::hasColumn('wishlists', 'vendor_id');
            
            // For vendor customers, ensure they can only add products from their vendor
            if ($isVendorCustomer && $hasVendorIdColumn && isset($product->vendor_id) && isset($user->vendor_id)) {
                if ($product->vendor_id !== $user->vendor_id) {
                    return $this->sendError('You can only add products from your vendor to wishlist.', [], 403);
                }
            }
            
            // Check if already in wishlist
            $existingQuery = Wishlist::where('product_id', $productId);
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $existingQuery->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $existingQuery->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $existingQuery->where('user_id', $user->id);
            }
            
            $existing = $existingQuery->first();
            
            if ($existing) {
                return $this->sendError('Product is already in your wishlist.', [
                    'wishlist_item_id' => $existing->id,
                    'added_at' => $existing->created_at
                ], 400);
            }
            
            // Add to wishlist
            $wishlistData = [
                'product_id' => $productId,
                'user_id' => $user->id,
            ];
            
            // Add vendor columns if they exist
            if ($hasVendorIdColumn && isset($product->vendor_id)) {
                $wishlistData['vendor_id'] = $product->vendor_id;
            }
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $wishlistData['vendor_customer_id'] = $user->id;
                    $wishlistData['user_id'] = null;
                } else {
                    $wishlistData['vendor_customer_id'] = null;
                }
            }
            
            $wishlistItem = Wishlist::create($wishlistData);
            
            if (!$wishlistItem) {
                return $this->sendError('Failed to add product to wishlist. Please try again.', [], 500);
            }
            
            $wishlistItem->load(['product', 'vendor']);
            
            return $this->sendResponse([
                'wishlist_item' => $wishlistItem,
                'vendor_info' => $wishlistItem->vendor ? [
                    'id' => $wishlistItem->vendor->id,
                    'store_name' => $wishlistItem->vendor->store_name,
                    'store_slug' => $wishlistItem->vendor->store_slug,
                ] : null,
            ], 'Product added to wishlist.', 201);
            
        } catch (\Exception $e) {
            \Log::error('Wishlist Add Error: ' . $e->getMessage(), [
                'product_id' => $productId,
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while adding product to wishlist.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove product from wishlist
     * 
     * @OA\Delete(
     *      path="/api/v1/wishlist/{productId}",
     *      operationId="removeFromWishlist",
     *      tags={"Wishlist"},
     *      summary="Remove product from wishlist",
     *      description="Remove a product from the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to remove",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product removed from wishlist",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Product removed from wishlist.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found in wishlist"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, $productId)
    {
        try {
            // Validate product ID
            if (!is_numeric($productId) || $productId <= 0) {
                return $this->sendError('Invalid product ID.', [], 400);
            }

            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            
            // Check if vendor_customer_id column exists
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            
            $query = Wishlist::where('product_id', $productId);
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $query->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $query->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $query->where('user_id', $user->id);
            }
            
            $wishlistItem = $query->first();
            
            if (!$wishlistItem) {
                return $this->sendError('Product not found in your wishlist.', [], 404);
            }
            
            // Store product info before deletion for response
            $productInfo = [
                'product_id' => $wishlistItem->product_id,
                'vendor_id' => $wishlistItem->vendor_id,
            ];
            
            $deleted = $wishlistItem->delete();
            
            if (!$deleted) {
                return $this->sendError('Failed to remove product from wishlist. Please try again.', [], 500);
            }
            
            return $this->sendResponse([
                'removed_item' => $productInfo
            ], 'Product removed from wishlist.');
            
        } catch (\Exception $e) {
            \Log::error('Wishlist Remove Error: ' . $e->getMessage(), [
                'product_id' => $productId,
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while removing product from wishlist.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if product is in wishlist
     * 
     * @OA\Get(
     *      path="/api/v1/wishlist/check/{productId}",
     *      operationId="checkWishlist",
     *      tags={"Wishlist"},
     *      summary="Check if product is in wishlist",
     *      description="Check if a specific product is in the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to check",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="is_in_wishlist", type="boolean", example=true),
     *                  @OA\Property(property="added_at", type="string", format="date-time", nullable=true),
     *              ),
     *              @OA\Property(property="message", type="string", example="Wishlist status retrieved.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request, $productId)
    {
        try {
            // Validate product ID
            if (!is_numeric($productId) || $productId <= 0) {
                return $this->sendError('Invalid product ID.', [], 400);
            }

            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            
            // Check if vendor_customer_id column exists
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            
            $query = Wishlist::where('product_id', $productId);
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $query->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $query->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $query->where('user_id', $user->id);
            }
            
            $wishlistItem = $query->first();
            
            return $this->sendResponse([
                'is_in_wishlist' => $wishlistItem !== null,
                'added_at' => $wishlistItem ? $wishlistItem->created_at : null,
                'vendor_id' => $wishlistItem ? $wishlistItem->vendor_id : null,
                'wishlist_item_id' => $wishlistItem ? $wishlistItem->id : null,
            ], 'Wishlist status retrieved.');
            
        } catch (\Exception $e) {
            \Log::error('Wishlist Check Error: ' . $e->getMessage(), [
                'product_id' => $productId,
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while checking wishlist status.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check multiple products in wishlist
     * 
     * @OA\Post(
     *      path="/api/v1/wishlist/check-multiple",
     *      operationId="checkMultipleWishlist",
     *      tags={"Wishlist"},
     *      summary="Check multiple products in wishlist",
     *      description="Check if multiple products are in the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="product_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="Wishlist status retrieved.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMultiple(Request $request)
    {
        try {
            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $request->validate([
                'product_ids' => 'required|array|min:1',
                'product_ids.*' => 'integer|min:1',
            ]);
            
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            $productIds = $request->product_ids;
            
            // Check if vendor_customer_id column exists
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            
            $query = Wishlist::whereIn('product_id', $productIds);
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $query->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $query->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $query->where('user_id', $user->id);
            }
            
            $wishlistItems = $query->get();
            
            // Create a map of product_id => wishlist status
            $statusMap = [];
            foreach ($productIds as $productId) {
                $item = $wishlistItems->firstWhere('product_id', $productId);
                $statusMap[$productId] = [
                    'is_in_wishlist' => $item !== null,
                    'added_at' => $item ? $item->created_at : null,
                    'vendor_id' => $item ? $item->vendor_id : null,
                    'wishlist_item_id' => $item ? $item->id : null,
                ];
            }
            
            return $this->sendResponse([
                'products' => $statusMap,
                'total_in_wishlist' => $wishlistItems->count(),
                'total_checked' => count($productIds),
            ], 'Wishlist status retrieved.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation failed.', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('Wishlist Check Multiple Error: ' . $e->getMessage(), [
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while checking wishlist status.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Move wishlist item to cart
     * 
     * @OA\Post(
     *      path="/api/v1/wishlist/{productId}/add-to-cart",
     *      operationId="wishlistToCart",
     *      tags={"Wishlist"},
     *      summary="Move wishlist item to cart",
     *      description="Add a product from wishlist to cart and optionally remove from wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to move to cart",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(property="quantity", type="integer", example=1, description="Quantity to add (default: 1)"),
     *              @OA\Property(property="remove_from_wishlist", type="boolean", example=true, description="Remove from wishlist after adding to cart (default: true)"),
     *              @OA\Property(property="variation_id", type="integer", nullable=true, description="Product variation ID (for variable products)"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Product added to cart",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="cart_item", type="object"),
     *                  @OA\Property(property="removed_from_wishlist", type="boolean"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Product added to cart.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Product out of stock or insufficient quantity"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Product not found in wishlist"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request, $productId)
    {
        try {
            // Validate product ID
            if (!is_numeric($productId) || $productId <= 0) {
                return $this->sendError('Invalid product ID.', [], 400);
            }

            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            $quantity = $request->quantity ?? 1;
            $removeFromWishlist = $request->remove_from_wishlist ?? true;
            $variationId = $request->variation_id;
            
            // Validate quantity
            if (!is_numeric($quantity) || $quantity <= 0) {
                return $this->sendError('Invalid quantity. Must be greater than 0.', [], 400);
            }
            
            // Check if vendor_customer_id column exists
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            
            // Check if product is in wishlist
            $query = Wishlist::where('product_id', $productId);
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $query->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $query->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $query->where('user_id', $user->id);
            }
            
            $wishlistItem = $query->first();
            
            if (!$wishlistItem) {
                return $this->sendError('Product not found in your wishlist.', [], 404);
            }
            
            // Get the product
            $product = Product::where('id', $productId)
                ->whereIn('status', ['published', 'active'])
                ->first();
            
            if (!$product) {
                return $this->sendError('Product is no longer available.', [], 404);
            }
            
            // Check stock
            if (!$product->in_stock || $product->stock_quantity < $quantity) {
                return $this->sendError('Product is out of stock or insufficient quantity available.', [
                    'available_quantity' => $product->stock_quantity ?? 0,
                    'requested_quantity' => $quantity,
                ], 400);
            }
            
            // Calculate price
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                ? $product->selling_price 
                : $product->mrp;
            
            // Calculate discounted price based on user type
            if ($isVendorCustomer && method_exists($user, 'getDiscountedPrice')) {
                $discountedPrice = $user->getDiscountedPrice($priceToUse);
            } else {
                $discountedPrice = function_exists('calculateDiscountedPrice') 
                    ? calculateDiscountedPrice($priceToUse, $user) 
                    : $priceToUse;
            }
            
            // Check if product already in cart
            $cartQuery = ShoppingCartItem::where('product_id', $productId);
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $cartQuery->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $cartQuery->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $cartQuery->where('user_id', $user->id);
            }
            
            if ($variationId) {
                $cartQuery->where('variation_id', $variationId);
            }
            
            $cartItem = $cartQuery->first();
            
            if ($cartItem) {
                // Update quantity
                $newQuantity = $cartItem->quantity + $quantity;
                
                // Check if new quantity exceeds stock
                if ($product->stock_quantity < $newQuantity) {
                    return $this->sendError('Cannot add more. Insufficient stock available.', [
                        'available_quantity' => $product->stock_quantity,
                        'current_cart_quantity' => $cartItem->quantity,
                        'requested_additional' => $quantity,
                    ], 400);
                }
                
                $cartItem->quantity = $newQuantity;
                $cartItem->price = $discountedPrice;
                $cartItem->save();
                
                // REDUCE STOCK QUANTITY by the quantity being added
                $product->decrement('stock_quantity', $quantity);
            } else {
                // Create new cart item
                $cartData = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $discountedPrice,
                    'user_id' => $user->id,
                ];
                
                // Add vendor_customer_id if column exists
                if ($hasVendorCustomerColumn) {
                    if ($isVendorCustomer) {
                        $cartData['vendor_customer_id'] = $user->id;
                        $cartData['user_id'] = null;
                    } else {
                        $cartData['vendor_customer_id'] = null;
                    }
                }
                
                if ($variationId) {
                    $cartData['variation_id'] = $variationId;
                }
                
                $cartItem = ShoppingCartItem::create($cartData);
                
                // REDUCE STOCK QUANTITY by the quantity being added
                $product->decrement('stock_quantity', $quantity);
            }
            
            // Update in_stock status if stock is depleted
            if ($product->fresh()->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
            
            // Remove from wishlist if requested
            $removedFromWishlist = false;
            if ($removeFromWishlist) {
                $wishlistItem->delete();
                $removedFromWishlist = true;
            }
            
            $cartItem->load('product');
            
            return $this->sendResponse([
                'cart_item' => $cartItem,
                'removed_from_wishlist' => $removedFromWishlist,
            ], 'Product added to cart.');
            
        } catch (\Exception $e) {
            \Log::error('Wishlist Add To Cart Error: ' . $e->getMessage(), [
                'product_id' => $productId,
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while adding product to cart.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear entire wishlist
     * 
     * @OA\Delete(
     *      path="/api/v1/wishlist/clear",
     *      operationId="clearWishlist",
     *      tags={"Wishlist"},
     *      summary="Clear entire wishlist",
     *      description="Remove all items from the authenticated user's wishlist",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="vendor_id",
     *          description="Clear only items from specific vendor (optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Wishlist cleared",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="items_removed", type="integer", example=5),
     *              ),
     *              @OA\Property(property="message", type="string", example="Wishlist cleared successfully.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        try {
            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            $vendorId = $request->vendor_id;
            
            // Validate vendor_id if provided
            if ($vendorId && (!is_numeric($vendorId) || $vendorId <= 0)) {
                return $this->sendError('Invalid vendor ID.', [], 400);
            }
            
            // Check if vendor_customer_id column exists
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            $hasVendorIdColumn = Schema::hasColumn('wishlists', 'vendor_id');
            
            $query = Wishlist::query();
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $query->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $query->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $query->where('user_id', $user->id);
            }
            
            // Filter by vendor if specified and column exists
            if ($vendorId && $hasVendorIdColumn) {
                $query->where('vendor_id', $vendorId);
            }
            
            $itemsRemoved = $query->delete();
            
            $message = $vendorId 
                ? 'Wishlist items from vendor cleared successfully.' 
                : 'Wishlist cleared successfully.';
            
            return $this->sendResponse([
                'items_removed' => $itemsRemoved,
            ], $message);
            
        } catch (\Exception $e) {
            \Log::error('Wishlist Clear Error: ' . $e->getMessage(), [
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while clearing wishlist.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get wishlist summary grouped by vendor
     * 
     * @OA\Get(
     *      path="/api/v1/wishlist/summary",
     *      operationId="getWishlistSummary",
     *      tags={"Wishlist"},
     *      summary="Get wishlist summary",
     *      description="Get wishlist summary with counts grouped by vendor",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="Wishlist summary retrieved.")
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        try {
            $user = $request->user();
            
            // Check if user is authenticated
            if (!$user) {
                return $this->sendError('Unauthenticated.', [], 401);
            }
            
            $isVendorCustomer = $user instanceof \App\Models\VendorCustomer;
            
            // Check if vendor_customer_id column exists
            $hasVendorCustomerColumn = Schema::hasColumn('wishlists', 'vendor_customer_id');
            
            $query = Wishlist::query();
            
            if ($hasVendorCustomerColumn) {
                if ($isVendorCustomer) {
                    $query->where('vendor_customer_id', $user->id)->whereNull('user_id');
                } else {
                    $query->where('user_id', $user->id)->whereNull('vendor_customer_id');
                }
            } else {
                $query->where('user_id', $user->id);
            }
            
            $wishlistItems = $query->with('vendor')->get();
            
            $totalItems = $wishlistItems->count();
            
            // Group by vendor
            $byVendor = $wishlistItems->groupBy('vendor_id')->map(function ($items, $vendorId) {
                $vendor = $items->first()->vendor;
                return [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor ? $vendor->store_name : 'Main Store',
                    'vendor_slug' => $vendor ? $vendor->store_slug : null,
                    'count' => $items->count(),
                ];
            })->values();
            
            return $this->sendResponse([
                'total_items' => $totalItems,
                'vendors_count' => $byVendor->count(),
                'by_vendor' => $byVendor,
                'user_type' => $isVendorCustomer ? 'vendor_customer' : 'regular_user',
            ], 'Wishlist summary retrieved.');
            
        } catch (\Exception $e) {
            \Log::error('Wishlist Summary Error: ' . $e->getMessage(), [
                'user_id' => $request->user() ? $request->user()->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->sendError('An error occurred while retrieving wishlist summary.', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // =============================================
    // VENDOR CUSTOMER SPECIFIC METHODS
    // These are aliases that call the main methods
    // but are explicitly named for vendor customer routes
    // =============================================
    
    /**
     * Get vendor customer's wishlist
     */
    public function vendorCustomerIndex(Request $request)
    {
        return $this->index($request);
    }
    
    /**
     * Get vendor customer's wishlist summary
     */
    public function vendorCustomerSummary(Request $request)
    {
        return $this->summary($request);
    }
    
    /**
     * Add product to vendor customer's wishlist
     */
    public function vendorCustomerAdd(Request $request, $productId)
    {
        return $this->add($request, $productId);
    }
    
    /**
     * Remove product from vendor customer's wishlist
     */
    public function vendorCustomerRemove(Request $request, $productId)
    {
        return $this->remove($request, $productId);
    }
    
    /**
     * Check if product is in vendor customer's wishlist
     */
    public function vendorCustomerCheck(Request $request, $productId)
    {
        return $this->check($request, $productId);
    }
    
    /**
     * Check multiple products in vendor customer's wishlist
     */
    public function vendorCustomerCheckMultiple(Request $request)
    {
        return $this->checkMultiple($request);
    }
    
    /**
     * Move vendor customer's wishlist item to cart
     */
    public function vendorCustomerAddToCart(Request $request, $productId)
    {
        return $this->addToCart($request, $productId);
    }
    
    /**
     * Clear vendor customer's wishlist
     */
    public function vendorCustomerClear(Request $request)
    {
        return $this->clear($request);
    }
}