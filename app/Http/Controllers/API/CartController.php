<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShoppingCartItem;
use App\Models\ProformaInvoice;
use App\Models\User;
use App\Models\Notification;
use App\Models\Coupon;
use App\Models\Vendor;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="API Endpoints for User Shopping Cart"
 * )
 */
class CartController extends ApiController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Add product to cart (alias for addToCart)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        return $this->addToCart($request);
    }

    /**
     * Remove item from cart (alias for destroy)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, $id)
    {
        return $this->destroy($request, $id);
    }

    /**
     * Get authenticated user's cart items
     * 
     * @OA\Get(
     *      path="/api/v1/cart",
     *      operationId="getCart",
     *      tags={"Cart"},
     *      summary="Get user's cart",
     *      description="Returns the authenticated user's shopping cart items with detailed totals",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
        $user = $request->user();
        
        $cartItems = ShoppingCartItem::where('user_id', $user->id)
            ->with(['product', 'variation'])
            ->get();
        
        // Filter out items with null products (deleted products)
        $validCartItems = $cartItems->filter(function ($item) {
            return $item->product !== null;
        });
        
        // Clean up invalid cart items (products that no longer exist)
        $invalidItemIds = $cartItems->filter(function ($item) {
            return $item->product === null;
        })->pluck('id');
        
        if ($invalidItemIds->isNotEmpty()) {
            ShoppingCartItem::whereIn('id', $invalidItemIds)->delete();
        }
        
        // Calculate subtotal (sum of all item prices * quantities)
        $subtotal = $validCartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        // Calculate original subtotal (before any discounts) for comparison
        $originalSubtotal = $validCartItems->sum(function ($item) {
            // Get original price (MRP or selling price before user discount)
            $product = $item->product;
            $variation = $item->variation;
            
            if ($variation) {
                $originalPrice = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) 
                    ? $variation->selling_price 
                    : $variation->mrp;
            } else {
                $originalPrice = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                    ? $product->selling_price 
                    : $product->mrp;
            }
            
            return $originalPrice * $item->quantity;
        });
        
        // Calculate discount amount (user-specific discount)
        $discountAmount = $originalSubtotal - $subtotal;
        
        // Calculate total quantity
        $totalQuantity = $validCartItems->sum('quantity');
        
        // Check for applied coupon and calculate coupon discount
        $couponDiscount = 0;
        $appliedCoupon = null;
        
        if ($user->applied_coupon_id) {
            $coupon = Coupon::find($user->applied_coupon_id);
            
            if ($coupon && $coupon->isValid() && $coupon->canBeUsedBy($user)) {
                // Check minimum order amount
                if (!$coupon->min_order_amount || $subtotal >= $coupon->min_order_amount) {
                    $couponDiscount = $coupon->calculateDiscount($subtotal);
                    $appliedCoupon = [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'discount_type' => $coupon->discount_type,
                        'discount_value' => $coupon->discount_value,
                        'discount_amount' => $couponDiscount,
                        'formatted_discount' => $coupon->formatted_discount,
                    ];
                } else {
                    // Coupon doesn't meet minimum order amount - clear it
                    $user->update(['applied_coupon_id' => null]);
                }
            } else {
                // Coupon is no longer valid - clear it
                $user->update(['applied_coupon_id' => null]);
            }
        }
        
        // Calculate final total (after coupon discount)
        $finalTotal = $subtotal - $couponDiscount;
        
        // Transform items to ensure consistent response format
        $transformedItems = $validCartItems->map(function ($item) {
            // Get original price for display
            $product = $item->product;
            $variation = $item->variation;
            
            if ($variation) {
                $originalPrice = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) 
                    ? $variation->selling_price 
                    : $variation->mrp;
                $mrp = $variation->mrp;
                $sku = $variation->sku;
            } else {
                $originalPrice = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                    ? $product->selling_price 
                    : $product->mrp;
                $mrp = $product->mrp;
                $sku = $product->sku ?? null;
            }
            
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'product_name' => $item->product->name ?? 'Unknown Product',
                'product_slug' => $item->product->slug ?? '',
                'sku' => $sku,
                'variation_name' => $item->variation ? $item->variation->display_name : null,
                'variation_attributes' => $item->variation ? $item->variation->attribute_values : null,
                'variation_formatted_attributes' => $item->variation ? $item->variation->formatted_attributes : null,
                'quantity' => $item->quantity,
                'mrp' => number_format($mrp, 2, '.', ''),
                'original_price' => number_format($originalPrice, 2, '.', ''),
                'price' => number_format($item->price, 2, '.', ''),
                'item_discount' => number_format($originalPrice - $item->price, 2, '.', ''),
                'total' => number_format($item->price * $item->quantity, 2, '.', ''),
                'main_photo_url' => $item->product->mainPhoto?->url ?? null,
                'in_stock' => $item->variation ? $item->variation->in_stock : ($item->product->in_stock ?? false),
                'stock_quantity' => $item->variation ? $item->variation->stock_quantity : ($item->product->stock_quantity ?? 0),
            ];
        })->values();
        
        return $this->sendResponse([
            'items' => $transformedItems,
            'summary' => [
                'item_count' => $validCartItems->count(),
                'total_quantity' => $totalQuantity,
                'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                'discount_amount' => number_format($discountAmount, 2, '.', ''),
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'coupon_discount' => number_format($couponDiscount, 2, '.', ''),
                'total' => number_format($finalTotal, 2, '.', ''),
            ],
            'applied_coupon' => $appliedCoupon,
            // Keep these for backward compatibility
            'total' => number_format($finalTotal, 2, '.', ''),
            'count' => $validCartItems->count(),
        ], 'Cart items retrieved successfully.');
    }

    /**
     * Add product to cart
     * 
     * @OA\Post(
     *      path="/api/v1/cart/add",
     *      operationId="addToCart",
     *      tags={"Cart"},
     *      summary="Add product to cart",
     *      description="Add a product to the authenticated user's cart",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"product_id"},
     *              @OA\Property(property="product_id", type="integer", example=1),
     *              @OA\Property(property="quantity", type="integer", example=1),
     *          ),
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
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        $variation = null;

        // Handle variable products with variations
        if ($request->has('product_variation_id') && $request->product_variation_id) {
            $variation = ProductVariation::findOrFail($request->product_variation_id);
            
            // Validate variation belongs to product
            if ($variation->product_id !== $product->id) {
                return $this->sendError('Invalid variation for this product.', [], 400);
            }
        }

        // Check if item already exists in cart to calculate total needed quantity
        $existingCartItem = ShoppingCartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('product_variation_id', $variation ? $variation->id : null)
            ->first();

        // Calculate total quantity needed (existing + new)
        $existingQuantity = $existingCartItem ? $existingCartItem->quantity : 0;
        $totalQuantityNeeded = $existingQuantity + $quantity;

        // Check stock based on product type
        if ($variation) {
            // Variable product - check variation stock
            if ($variation->stock_quantity < $quantity) {
                return $this->sendError('Variation is out of stock or insufficient quantity available.', [], 400);
            }
        } else {
            // Simple product - check product stock
            if (!$product->in_stock || $product->stock_quantity < $quantity) {
                return $this->sendError('Product is out of stock or insufficient quantity available.', [], 400);
            }
        }

        // Calculate discounted price
        if ($variation) {
            $priceToUse = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) 
                ? $variation->selling_price 
                : $variation->mrp;
        } else {
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                ? $product->selling_price 
                : $product->mrp;
        }
        
        // Apply user discount if available
        $discountedPrice = function_exists('calculateDiscountedPrice') 
            ? calculateDiscountedPrice($priceToUse, $user) 
            : $priceToUse;

        // Add or update cart item
        $cartItem = ShoppingCartItem::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'product_variation_id' => $variation ? $variation->id : null,
            ],
            [
                'quantity' => $totalQuantityNeeded,
                'price' => $discountedPrice,
            ]
        );

        // REDUCE STOCK QUANTITY by the quantity being added (not total)
        if ($variation) {
            $variation->decrement('stock_quantity', $quantity);
        } else {
            $product->decrement('stock_quantity', $quantity);
            
            // Update in_stock status if stock is depleted
            if ($product->fresh()->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
        }

        // Get updated cart count and totals
        $cartItems = ShoppingCartItem::where('user_id', $user->id)
            ->with(['product', 'variation'])
            ->get();
        
        // Calculate subtotal
        $cartSubtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        // Calculate original subtotal (before discounts)
        $originalSubtotal = $cartItems->sum(function ($item) {
            $prod = $item->product;
            $var = $item->variation;
            
            if ($var) {
                $origPrice = (!is_null($var->selling_price) && $var->selling_price !== '' && $var->selling_price >= 0) 
                    ? $var->selling_price 
                    : $var->mrp;
            } else if ($prod) {
                $origPrice = (!is_null($prod->selling_price) && $prod->selling_price !== '' && $prod->selling_price >= 0) 
                    ? $prod->selling_price 
                    : $prod->mrp;
            } else {
                $origPrice = $item->price;
            }
            
            return $origPrice * $item->quantity;
        });
        
        $discountAmountTotal = $originalSubtotal - $cartSubtotal;
        $totalQuantity = $cartItems->sum('quantity');
        $cartCount = $cartItems->count();

        // Load relationships for response
        $cartItem->load('product', 'variation');
        
        // Get original price for the item
        if ($variation) {
            $originalPrice = $priceToUse;
            $mrp = $variation->mrp;
        } else {
            $originalPrice = $priceToUse;
            $mrp = $product->mrp;
        }
        
        // Format the cart item response with product details
        $formattedCartItem = [
            'id' => $cartItem->id,
            'product_id' => $cartItem->product_id,
            'product_variation_id' => $cartItem->product_variation_id,
            'product_name' => $cartItem->product->name ?? 'Unknown Product',
            'product_slug' => $cartItem->product->slug ?? '',
            'variation_name' => $cartItem->variation ? $cartItem->variation->display_name : null,
            'variation_attributes' => $cartItem->variation ? $cartItem->variation->attribute_values : null,
            'quantity' => $cartItem->quantity,
            'mrp' => number_format($mrp, 2, '.', ''),
            'original_price' => number_format($originalPrice, 2, '.', ''),
            'price' => number_format($cartItem->price, 2, '.', ''),
            'item_discount' => number_format($originalPrice - $cartItem->price, 2, '.', ''),
            'total' => number_format($cartItem->price * $cartItem->quantity, 2, '.', ''),
            'main_photo_url' => $cartItem->product->mainPhoto?->url ?? null,
            'in_stock' => $cartItem->variation ? $cartItem->variation->in_stock : ($cartItem->product->in_stock ?? false),
            'stock_quantity' => $cartItem->variation ? $cartItem->variation->stock_quantity : ($cartItem->product->stock_quantity ?? 0),
        ];

        return $this->sendResponse([
            'cart_item' => $formattedCartItem,
            'cart_count' => $cartCount,
            'summary' => [
                'item_count' => $cartCount,
                'total_quantity' => $totalQuantity,
                'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                'discount_amount' => number_format($discountAmountTotal, 2, '.', ''),
                'subtotal' => number_format($cartSubtotal, 2, '.', ''),
                'total' => number_format($cartSubtotal, 2, '.', ''),
            ],
        ], 'Product added to cart successfully.');
    }

    /**
     * Update cart item quantity
     * 
     * @OA\Put(
     *      path="/api/v1/cart/{id}",
     *      operationId="updateCartItem",
     *      tags={"Cart"},
     *      summary="Update cart item",
     *      description="Update the quantity of a cart item",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Cart item id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"quantity"},
     *              @OA\Property(property="quantity", type="integer", example=2),
     *          ),
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
     *          description="Not Found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        
        $cartItem = ShoppingCartItem::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return $this->sendError('Cart item not found.', [], 404);
        }

        $product = $cartItem->product;
        $variation = $cartItem->variation;
        $oldQuantity = $cartItem->quantity;
        $newQuantity = $request->quantity;
        $quantityDifference = $newQuantity - $oldQuantity;

        // If increasing quantity, check if enough stock is available
        if ($quantityDifference > 0) {
            if ($variation) {
                // Check variation stock
                if ($variation->stock_quantity < $quantityDifference) {
                    return $this->sendError('Variation is out of stock or insufficient quantity available. Only ' . $variation->stock_quantity . ' more available.', [], 400);
                }
                // Reduce variation stock by the difference
                $variation->decrement('stock_quantity', $quantityDifference);
            } else {
                // Check product stock
                if ($product->stock_quantity < $quantityDifference) {
                    return $this->sendError('Product is out of stock or insufficient quantity available. Only ' . $product->stock_quantity . ' more available.', [], 400);
                }
                // Reduce product stock by the difference
                $product->decrement('stock_quantity', $quantityDifference);
            }
        } elseif ($quantityDifference < 0) {
            // Restore stock by the difference (absolute value)
            if ($variation) {
                $variation->increment('stock_quantity', abs($quantityDifference));
            } else {
                $product->increment('stock_quantity', abs($quantityDifference));
            }
        }

        // Update in_stock status based on new stock quantity (for simple products only)
        if (!$variation) {
            $product->refresh();
            if ($product->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            } elseif ($product->stock_quantity > 0 && !$product->in_stock) {
                $product->update(['in_stock' => true]);
            }
        }

        $cartItem->update(['quantity' => $newQuantity]);

        // Calculate totals
        $itemTotal = $cartItem->price * $cartItem->quantity;
        $cartItems = ShoppingCartItem::where('user_id', $user->id)
            ->with(['product', 'variation'])
            ->get();
        
        // Calculate subtotal
        $cartSubtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        // Calculate original subtotal (before discounts)
        $originalSubtotal = $cartItems->sum(function ($item) {
            $product = $item->product;
            $variation = $item->variation;
            
            if ($variation) {
                $originalPrice = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) 
                    ? $variation->selling_price 
                    : $variation->mrp;
            } else if ($product) {
                $originalPrice = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                    ? $product->selling_price 
                    : $product->mrp;
            } else {
                $originalPrice = $item->price;
            }
            
            return $originalPrice * $item->quantity;
        });
        
        $discountAmount = $originalSubtotal - $cartSubtotal;
        $totalQuantity = $cartItems->sum('quantity');

        // Refresh cart item and load relationships
        $cartItem = $cartItem->fresh();
        $cartItem->load('product', 'variation');
        
        // Get original price for the item
        if ($cartItem->variation) {
            $originalPrice = (!is_null($cartItem->variation->selling_price) && $cartItem->variation->selling_price !== '' && $cartItem->variation->selling_price >= 0) 
                ? $cartItem->variation->selling_price 
                : $cartItem->variation->mrp;
            $mrp = $cartItem->variation->mrp;
        } else {
            $originalPrice = (!is_null($cartItem->product->selling_price) && $cartItem->product->selling_price !== '' && $cartItem->product->selling_price >= 0) 
                ? $cartItem->product->selling_price 
                : $cartItem->product->mrp;
            $mrp = $cartItem->product->mrp;
        }
        
        // Format the cart item response with product details
        $formattedCartItem = [
            'id' => $cartItem->id,
            'product_id' => $cartItem->product_id,
            'product_variation_id' => $cartItem->product_variation_id,
            'product_name' => $cartItem->product->name ?? 'Unknown Product',
            'product_slug' => $cartItem->product->slug ?? '',
            'variation_name' => $cartItem->variation ? $cartItem->variation->display_name : null,
            'variation_attributes' => $cartItem->variation ? $cartItem->variation->attribute_values : null,
            'quantity' => $cartItem->quantity,
            'mrp' => number_format($mrp, 2, '.', ''),
            'original_price' => number_format($originalPrice, 2, '.', ''),
            'price' => number_format($cartItem->price, 2, '.', ''),
            'item_discount' => number_format($originalPrice - $cartItem->price, 2, '.', ''),
            'total' => number_format($cartItem->price * $cartItem->quantity, 2, '.', ''),
            'main_photo_url' => $cartItem->product->mainPhoto?->url ?? null,
            'in_stock' => $cartItem->variation ? $cartItem->variation->in_stock : ($cartItem->product->in_stock ?? false),
            'stock_quantity' => $cartItem->variation ? $cartItem->variation->stock_quantity : ($cartItem->product->stock_quantity ?? 0),
        ];

        return $this->sendResponse([
            'cart_item' => $formattedCartItem,
            'item_total' => number_format($itemTotal, 2, '.', ''),
            'summary' => [
                'item_count' => $cartItems->count(),
                'total_quantity' => $totalQuantity,
                'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                'discount_amount' => number_format($discountAmount, 2, '.', ''),
                'subtotal' => number_format($cartSubtotal, 2, '.', ''),
                'total' => number_format($cartSubtotal, 2, '.', ''),
            ],
            // Keep for backward compatibility
            'cart_total' => number_format($cartSubtotal, 2, '.', ''),
        ], 'Cart item updated successfully.');
    }

    /**
     * Remove item from cart
     * 
     * @OA\Delete(
     *      path="/api/v1/cart/{id}",
     *      operationId="removeFromCart",
     *      tags={"Cart"},
     *      summary="Remove from cart",
     *      description="Remove an item from the cart",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Cart item id",
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
     *          description="Not Found"
     *      )
     * )
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $cartItem = ShoppingCartItem::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return $this->sendError('Cart item not found.', [], 404);
        }

        // RESTORE STOCK QUANTITY before deleting the cart item
        $product = $cartItem->product;
        $variation = $cartItem->variation;
        
        if ($variation) {
            // Restore variation stock
            $variation->increment('stock_quantity', $cartItem->quantity);
        } elseif ($product) {
            // Restore product stock
            $product->increment('stock_quantity', $cartItem->quantity);
            
            // Update in_stock status if stock was restored
            if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                $product->update(['in_stock' => true]);
            }
        }

        $cartItem->delete();

        // Get updated cart info
        $cartItems = ShoppingCartItem::where('user_id', $user->id)
            ->with(['product', 'variation'])
            ->get();
        
        // Calculate subtotal
        $cartSubtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        // Calculate original subtotal (before discounts)
        $originalSubtotal = $cartItems->sum(function ($item) {
            $product = $item->product;
            $variation = $item->variation;
            
            if ($variation) {
                $originalPrice = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) 
                    ? $variation->selling_price 
                    : $variation->mrp;
            } else if ($product) {
                $originalPrice = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                    ? $product->selling_price 
                    : $product->mrp;
            } else {
                $originalPrice = $item->price;
            }
            
            return $originalPrice * $item->quantity;
        });
        
        $discountAmount = $originalSubtotal - $cartSubtotal;
        $totalQuantity = $cartItems->sum('quantity');

        return $this->sendResponse([
            'cart_count' => $cartItems->count(),
            'summary' => [
                'item_count' => $cartItems->count(),
                'total_quantity' => $totalQuantity,
                'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                'discount_amount' => number_format($discountAmount, 2, '.', ''),
                'subtotal' => number_format($cartSubtotal, 2, '.', ''),
                'total' => number_format($cartSubtotal, 2, '.', ''),
            ],
            // Keep for backward compatibility
            'cart_total' => number_format($cartSubtotal, 2, '.', ''),
        ], 'Item removed from cart successfully.');
    }

    /**
     * Get cart items count
     * 
     * @OA\Get(
     *      path="/api/v1/cart/count",
     *      operationId="getCartCount",
     *      tags={"Cart"},
     *      summary="Get cart count",
     *      description="Get the number of items in the cart",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
    public function count(Request $request)
    {
        $user = $request->user();
        $cartCount = ShoppingCartItem::where('user_id', $user->id)->count();
        
        return $this->sendResponse(['cart_count' => $cartCount], 'Cart count retrieved successfully.');
    }

    /**
     * Generate proforma invoice from cart
     * 
     * @OA\Post(
     *      path="/api/v1/cart/generate-invoice",
     *      operationId="generateInvoice",
     *      tags={"Cart"},
     *      summary="Generate proforma invoice",
     *      description="Generate a proforma invoice from the cart items",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Cart is empty"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateInvoice(Request $request)
    {
        $user = $request->user();
        
        $cartItems = ShoppingCartItem::where('user_id', $user->id)
            ->with(['product', 'product.vendor', 'variation'])
            ->get();

        if ($cartItems->isEmpty()) {
            return $this->sendError('Cart is empty.', [], 400);
        }

        $invoiceDate = now()->format('Y-m-d');
        
        // Calculate cart subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        // Check for applied coupon (same as web functionality)
        $couponDiscount = 0;
        $couponData = null;
        $couponToRecord = null;
        
        if ($user->applied_coupon_id) {
            $coupon = Coupon::find($user->applied_coupon_id);
            
            // Verify coupon is still valid
            if ($coupon && $coupon->isValid() && $coupon->canBeUsedBy($user)) {
                // Check minimum order amount
                if (!$coupon->min_order_amount || $subtotal >= $coupon->min_order_amount) {
                    $couponDiscount = $coupon->calculateDiscount($subtotal);
                    $couponData = [
                        'id' => $coupon->id,
                        'code' => $coupon->code,
                        'discount_type' => $coupon->discount_type,
                        'discount_value' => $coupon->discount_value,
                        'discount_amount' => $couponDiscount,
                    ];
                    
                    // Store coupon for recording usage after invoice creation
                    $couponToRecord = $coupon;
                }
            }
        }
        
        // Calculate final total
        $total = $subtotal - $couponDiscount;
        
        // Group cart items by vendor to create separate invoices per vendor
        $itemsByVendor = $cartItems->groupBy(function ($item) {
            return $item->product->vendor_id ?? 0; // 0 for non-vendor products
        });
        
        $createdInvoices = [];
        $vendorNotifications = [];
        
        foreach ($itemsByVendor as $vendorId => $vendorItems) {
            // Calculate subtotal for this vendor's items
            $vendorSubtotal = $vendorItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });
            
            // Calculate proportional coupon discount for this vendor (same as web)
            $vendorCouponDiscount = 0;
            $vendorCouponData = null;
            if ($couponDiscount > 0 && $subtotal > 0) {
                $proportion = $vendorSubtotal / $subtotal;
                $vendorCouponDiscount = round($couponDiscount * $proportion, 2);
                if ($couponData) {
                    $vendorCouponData = array_merge($couponData, ['discount_amount' => $vendorCouponDiscount]);
                }
            }
            
            $vendorTotal = $vendorSubtotal - $vendorCouponDiscount;

            // Prepare invoice data for this vendor (matching web structure)
            $invoiceData = [
                'cart_items' => $vendorItems->map(function ($item) {
                    $itemData = [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_slug' => $item->product->slug,
                        'product_description' => $item->product->description,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->price * $item->quantity,
                        'vendor_id' => $item->product->vendor_id,
                    ];
                    
                    // Add variation details if this is a variable product
                    if ($item->product_variation_id && $item->variation) {
                        $itemData['product_variation_id'] = $item->product_variation_id;
                        $itemData['variation_display_name'] = $item->variation->display_name;
                        $itemData['variation_attributes'] = $item->variation->formatted_attributes;
                        $itemData['variation_sku'] = $item->variation->sku;
                    }
                    
                    return $itemData;
                })->toArray(),
                'subtotal' => $vendorSubtotal,
                'coupon' => $vendorCouponData,
                'coupon_discount' => $vendorCouponDiscount,
                'total' => $vendorTotal,
                'invoice_date' => $invoiceDate,
                'customer' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'address' => $user->address,
                    'mobile_number' => $user->mobile_number,
                ],
            ];

            // Create proforma invoice with retry logic for duplicate invoice numbers
            $proformaInvoice = $this->createProformaInvoiceWithRetry($user->id, $vendorTotal, $invoiceData, $vendorId > 0 ? $vendorId : null);
            $createdInvoices[] = $proformaInvoice;
            
            // Store vendor info for notifications
            if ($vendorId > 0) {
                $vendorNotifications[$vendorId] = [
                    'invoice' => $proformaInvoice,
                    'vendor_id' => $vendorId
                ];
                
                // Add the user as a customer of this vendor
                \App\Models\VendorCustomer::addCustomerToVendor($vendorId, $user->id, $proformaInvoice->id);
            }
        }
        
        // Use the first invoice for coupon recording and main notification
        $mainInvoice = $createdInvoices[0] ?? null;
        $invoiceNumber = $mainInvoice ? $mainInvoice->invoice_number : 'N/A';
        
        // Record coupon usage after invoice is created (so we can link to invoice)
        if ($couponToRecord && $couponDiscount > 0 && $mainInvoice) {
            $couponToRecord->recordUsage($user, $couponDiscount, $mainInvoice->id);
        }

        // Create notifications for admin users
        $adminUsers = User::whereIn('user_role', ['admin', 'super_admin'])->get();
        foreach ($adminUsers as $adminUser) {
            Notification::create([
                'user_id' => $adminUser->id,
                'title' => 'New Proforma Invoice Created',
                'message' => 'A new proforma invoice #' . $invoiceNumber . ' has been created by ' . $user->name,
                'type' => 'proforma_invoice',
                'data' => json_encode([
                    'invoice_id' => $mainInvoice->id,
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $user->name,
                    'customer_avatar' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : null,
                ]),
                'read' => false,
            ]);

            // Send push notification if device token exists
            if (!empty($adminUser->device_token)) {
                $payload = [
                    'notification' => [
                        'title' => 'New Proforma Invoice Created',
                        'body' => 'A new proforma invoice #' . $invoiceNumber . ' has been created by ' . $user->name,
                    ],
                    'data' => [
                        'invoice_id' => $mainInvoice->id,
                        'invoice_number' => $invoiceNumber,
                        'type' => 'proforma_invoice_created',
                    ],
                ];
                $this->notificationService->sendPushNotification($adminUser->device_token, $payload);
            }
        }
        
        // Send notifications to vendors for their respective invoices
        foreach ($vendorNotifications as $vendorId => $data) {
            $vendor = Vendor::find($vendorId);
            if ($vendor && $vendor->user) {
                $vendorInvoice = $data['invoice'];
                
                // Create database notification for vendor
                Notification::create([
                    'user_id' => $vendor->user_id,
                    'title' => 'New Order Invoice Received',
                    'message' => 'A new proforma invoice #' . $vendorInvoice->invoice_number . ' has been created for your store by ' . $user->name,
                    'type' => 'vendor_proforma_invoice',
                    'data' => json_encode([
                        'invoice_id' => $vendorInvoice->id,
                        'invoice_number' => $vendorInvoice->invoice_number,
                        'customer_name' => $user->name,
                        'customer_avatar' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : null,
                        'total_amount' => $vendorInvoice->total_amount,
                        'vendor_id' => $vendorId
                    ]),
                    'read' => false,
                ]);
                
                // Send push notification to vendor if they have device token
                if (!empty($vendor->user->device_token)) {
                    $payload = [
                        'notification' => [
                            'title' => 'New Order Invoice Received',
                            'body' => 'A new proforma invoice #' . $vendorInvoice->invoice_number . ' worth ₹' . number_format($vendorInvoice->total_amount, 2) . ' has been created for your store'
                        ],
                        'data' => [
                            'invoice_id' => $vendorInvoice->id,
                            'invoice_number' => $vendorInvoice->invoice_number,
                            'type' => 'vendor_proforma_invoice_created'
                        ]
                    ];
                    
                    $this->notificationService->sendPushNotification($vendor->user->device_token, $payload);
                }
            }
        }

        // Clear the cart
        ShoppingCartItem::where('user_id', $user->id)->delete();
        
        // Clear the applied coupon from user
        $user->update(['applied_coupon_id' => null]);

        return $this->sendResponse([
            'invoices' => $createdInvoices,
            'invoice' => $mainInvoice, // Keep for backward compatibility
            'total_invoices' => count($createdInvoices),
            'coupon_applied' => $couponData !== null,
            'coupon_discount' => $couponDiscount,
        ], 'Proforma invoice(s) generated successfully.', 201);
    }

    /**
     * Generate a serialized invoice number with database locking to prevent duplicates
     *
     * @return string
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        // Use database locking to prevent race conditions
        return \Illuminate\Support\Facades\DB::transaction(function () use ($year, $prefix) {
            // Lock the table for reading to prevent concurrent reads
            $latestInvoice = ProformaInvoice::where('invoice_number', 'like', $prefix . '%')
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latestInvoice) {
                $parts = explode('-', $latestInvoice->invoice_number);
                if (count($parts) >= 3 && $parts[1] == $year) {
                    $sequence = (int)$parts[2] + 1;
                } else {
                    $sequence = 1;
                }
            } else {
                $sequence = 1;
            }

            return "INV-{$year}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Create a proforma invoice with retry logic to handle duplicate invoice numbers.
     *
     * @param  int  $userId
     * @param  float  $total
     * @param  array  $invoiceData
     * @param  int|null  $vendorId
     * @param  int  $maxRetries
     * @return \App\Models\ProformaInvoice
     * @throws \Exception
     */
    private function createProformaInvoiceWithRetry($userId, $total, $invoiceData, $vendorId = null, $maxRetries = 5)
    {
        $attempts = 0;
        $lastException = null;
        
        while ($attempts < $maxRetries) {
            try {
                return \Illuminate\Support\Facades\DB::transaction(function () use ($userId, $total, $invoiceData, $vendorId) {
                    // Generate invoice number inside the transaction
                    $invoiceNumber = $this->generateInvoiceNumber();
                    
                    // Create the proforma invoice
                    return ProformaInvoice::create([
                        'invoice_number' => $invoiceNumber,
                        'user_id' => $userId,
                        'vendor_id' => $vendorId,
                        'total_amount' => $total,
                        'invoice_data' => $invoiceData,
                        'status' => ProformaInvoice::STATUS_DRAFT ?? 'draft',
                    ]);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                $lastException = $e;
                
                // Check if it's a duplicate entry error (MySQL error code 1062)
                if ($e->errorInfo[1] == 1062) {
                    $attempts++;
                    // Small delay before retry to reduce collision chance
                    usleep(100000 * $attempts); // 100ms * attempt number
                    continue;
                }
                
                // If it's not a duplicate entry error, rethrow
                throw $e;
            }
        }
        
        // If we've exhausted all retries, throw the last exception
        throw $lastException ?? new \Exception('Failed to create proforma invoice after ' . $maxRetries . ' attempts');
    }

    /**
     * Clear all items from cart
     * 
     * @OA\Delete(
     *      path="/api/v1/cart/clear",
     *      operationId="clearCart",
     *      tags={"Cart"},
     *      summary="Clear cart",
     *      description="Remove all items from the cart and restore stock",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
        $user = $request->user();
        
        // Get all cart items to restore stock
        $cartItems = ShoppingCartItem::where('user_id', $user->id)->with(['product', 'variation'])->get();
        
        // RESTORE STOCK for all items before clearing
        foreach ($cartItems as $cartItem) {
            // Check if this is a variable product with variation
            if ($cartItem->product_variation_id && $cartItem->variation) {
                // Restore variation stock
                $cartItem->variation->increment('stock_quantity', $cartItem->quantity);
                
                // Update variation in_stock status if stock was restored
                if ($cartItem->variation->fresh()->stock_quantity > 0 && !$cartItem->variation->in_stock) {
                    $cartItem->variation->update(['in_stock' => true]);
                }
            } else {
                // Restore simple product stock
                $product = $cartItem->product;
                if ($product) {
                    $product->increment('stock_quantity', $cartItem->quantity);
                    
                    // Update in_stock status if stock was restored
                    if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                        $product->update(['in_stock' => true]);
                    }
                }
            }
        }
        
        // Delete all cart items
        ShoppingCartItem::where('user_id', $user->id)->delete();
        
        return $this->sendResponse([
            'items_removed' => $cartItems->count(),
        ], 'Cart cleared and stock restored successfully.');
    }
}
