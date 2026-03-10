<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShoppingCartItem;
use App\Models\ProformaInvoice;
use App\Models\VendorCustomer;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Customer Cart",
 *     description="API Endpoints for Vendor Customer Shopping Cart"
 * )
 */
class CustomerCartController extends Controller
{
    /**
     * Get the authenticated customer
     */
    private function getCustomer(Request $request): VendorCustomer
    {
        return $request->user();
    }

    /**
     * Get customer's cart items
     */
    public function index(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        // Get cart items for this vendor customer with eager loading
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->with(['product', 'product.vendor', 'variation'])
            ->get();
        
        // Filter out items with null products and ensure product belongs to customer's vendor
        $validCartItems = $cartItems->filter(function ($item) use ($customer) {
            return $item->product && $item->product->vendor_id == $customer->vendor_id;
        });
        
        // Clean up invalid cart items (products that no longer exist or don't belong to vendor)
        $invalidItemIds = $cartItems->filter(function ($item) use ($customer) {
            return !$item->product || $item->product->vendor_id != $customer->vendor_id;
        })->pluck('id');
        
        if ($invalidItemIds->isNotEmpty()) {
            ShoppingCartItem::whereIn('id', $invalidItemIds)->delete();
        }
        
        // Calculate subtotal (sum of all item prices * quantities - after customer discount)
        $subtotal = $validCartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        
        // Calculate original subtotal (before customer discount)
        $originalSubtotal = $validCartItems->sum(function ($item) {
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
        
        // Calculate discount amount
        $discountAmount = $originalSubtotal - $subtotal;
        
        // Calculate total quantity
        $totalQuantity = $validCartItems->sum('quantity');
        
        $transformedItems = $validCartItems->map(function ($item) use ($customer) {
            $product = $item->product;
            $variation = $item->variation;
            
            // Get original price for display
            if ($variation) {
                $originalPrice = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) 
                    ? $variation->selling_price 
                    : $variation->mrp;
                $mrp = $variation->mrp;
            } else {
                $originalPrice = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                    ? $product->selling_price 
                    : $product->mrp;
                $mrp = $product->mrp;
            }
            
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'product_name' => $item->product->name ?? 'Unknown Product',
                'product_slug' => $item->product->slug ?? '',
                'variation_name' => $item->variation ? $item->variation->display_name : null,
                'variation_attributes' => $item->variation ? $item->variation->attribute_values : null,
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
        
        return response()->json([
            'success' => true,
            'message' => 'Cart items retrieved successfully',
            'data' => [
                'items' => $transformedItems,
                'summary' => [
                    'item_count' => $validCartItems->count(),
                    'total_quantity' => $totalQuantity,
                    'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                    'discount_amount' => number_format($discountAmount, 2, '.', ''),
                    'subtotal' => number_format($subtotal, 2, '.', ''),
                    'total' => number_format($subtotal, 2, '.', ''),
                ],
                // Keep for backward compatibility
                'total' => number_format($subtotal, 2, '.', ''),
                'count' => $validCartItems->count(),
                'customer_discount' => $customer->discount_percentage,
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $customer = $this->getCustomer($request);
        $product = Product::find($request->product_id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ], 404);
        }
        
        $quantity = $request->quantity ?? 1;
        $variation = null;

        // Ensure product belongs to customer's vendor
        if ($product->vendor_id != $customer->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Product not available',
                'data' => null
            ], 404);
        }

        // Handle variable products with variations
        if ($request->has('product_variation_id') && $request->product_variation_id) {
            $variation = ProductVariation::find($request->product_variation_id);
            
            if (!$variation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product variation not found',
                    'data' => null
                ], 404);
            }
            
            // Validate variation belongs to product
            if ($variation->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid variation for this product',
                    'data' => null
                ], 400);
            }
        }

        // Check if item already exists in cart
        $existingCartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('product_variation_id', $variation ? $variation->id : null)
            ->first();

        $existingQuantity = $existingCartItem ? $existingCartItem->quantity : 0;
        $totalQuantityNeeded = $existingQuantity + $quantity;

        // Check stock
        if ($variation) {
            if ($variation->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Only ' . $variation->stock_quantity . ' available.',
                    'data' => null
                ], 400);
            }
        } else {
            if (!$product->in_stock || $product->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock. Only ' . $product->stock_quantity . ' available.',
                    'data' => null
                ], 400);
            }
        }

        // Calculate price with customer discount
        if ($variation) {
            $basePrice = $variation->selling_price ?? $variation->mrp;
        } else {
            $basePrice = $product->selling_price ?? $product->mrp;
        }
        
        $discountedPrice = $customer->getDiscountedPrice($basePrice);

        // Add or update cart item
        $cartItem = ShoppingCartItem::updateOrCreate(
            [
                'vendor_customer_id' => $customer->id,
                'product_id' => $product->id,
                'product_variation_id' => $variation ? $variation->id : null,
            ],
            [
                'quantity' => $totalQuantityNeeded,
                'price' => $discountedPrice,
            ]
        );

        // Reduce stock
        if ($variation) {
            $variation->decrement('stock_quantity', $quantity);
            if ($variation->fresh()->stock_quantity <= 0) {
                $variation->update(['in_stock' => false]);
            }
        } else {
            $product->decrement('stock_quantity', $quantity);
            if ($product->fresh()->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
        }

        // Get updated cart totals
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
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

        // Load product for response
        $product->load();
        
        // Get original price for the item
        if ($variation) {
            $originalPrice = $basePrice;
            $mrp = $variation->mrp;
        } else {
            $originalPrice = $basePrice;
            $mrp = $product->mrp;
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'data' => [
                'cart_item' => [
                    'id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'product_variation_id' => $cartItem->product_variation_id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug ?? '',
                    'variation_name' => $variation ? $variation->display_name : null,
                    'variation_attributes' => $variation ? $variation->attribute_values : null,
                    'quantity' => $cartItem->quantity,
                    'mrp' => number_format($mrp, 2, '.', ''),
                    'original_price' => number_format($originalPrice, 2, '.', ''),
                    'price' => number_format($cartItem->price, 2, '.', ''),
                    'item_discount' => number_format($originalPrice - $cartItem->price, 2, '.', ''),
                    'total' => number_format($cartItem->price * $cartItem->quantity, 2, '.', ''),
                    'main_photo_url' => $product->mainPhoto?->url ?? null,
                    'in_stock' => $variation ? $variation->in_stock : ($product->in_stock ?? false),
                    'stock_quantity' => $variation ? $variation->stock_quantity : ($product->stock_quantity ?? 0),
                ],
                'cart_count' => $cartCount,
                'summary' => [
                    'item_count' => $cartCount,
                    'total_quantity' => $totalQuantity,
                    'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                    'discount_amount' => number_format($discountAmountTotal, 2, '.', ''),
                    'subtotal' => number_format($cartSubtotal, 2, '.', ''),
                    'total' => number_format($cartSubtotal, 2, '.', ''),
                ],
            ]
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $customer = $this->getCustomer($request);
        
        $cartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->where('id', $id)
            ->with(['product', 'variation'])
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ], 404);
        }

        $product = $cartItem->product;
        $variation = $cartItem->variation;
        
        if (!$product) {
            // Product no longer exists, remove cart item
            $cartItem->delete();
            return response()->json([
                'success' => false,
                'message' => 'Product no longer available',
                'data' => null
            ], 404);
        }
        
        $oldQuantity = $cartItem->quantity;
        $newQuantity = $request->quantity;
        $quantityDifference = $newQuantity - $oldQuantity;

        // If increasing quantity, check stock
        if ($quantityDifference > 0) {
            if ($variation) {
                if ($variation->stock_quantity < $quantityDifference) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Only ' . $variation->stock_quantity . ' more available.',
                        'data' => null
                    ], 400);
                }
                $variation->decrement('stock_quantity', $quantityDifference);
            } else {
                if ($product->stock_quantity < $quantityDifference) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Only ' . $product->stock_quantity . ' more available.',
                        'data' => null
                    ], 400);
                }
                $product->decrement('stock_quantity', $quantityDifference);
            }
        } elseif ($quantityDifference < 0) {
            // Restore stock
            if ($variation) {
                $variation->increment('stock_quantity', abs($quantityDifference));
                if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                    $variation->update(['in_stock' => true]);
                }
            } else {
                $product->increment('stock_quantity', abs($quantityDifference));
                if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                    $product->update(['in_stock' => true]);
                }
            }
        }

        // Update in_stock status
        if (!$variation) {
            $product->refresh();
            if ($product->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
        }

        $cartItem->update(['quantity' => $newQuantity]);

        // Calculate totals
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
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

        // Refresh and load relationships for response
        // Note: mainPhoto is an accessor on Product, not a relationship
        $cartItem = $cartItem->fresh();
        $cartItem->load('variation');
        
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

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => [
                'cart_item' => [
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
                ],
                'summary' => [
                    'item_count' => $cartItems->count(),
                    'total_quantity' => $totalQuantity,
                    'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                    'discount_amount' => number_format($discountAmountTotal, 2, '.', ''),
                    'subtotal' => number_format($cartSubtotal, 2, '.', ''),
                    'total' => number_format($cartSubtotal, 2, '.', ''),
                ],
                // Keep for backward compatibility
                'cart_total' => number_format($cartSubtotal, 2, '.', ''),
            ]
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $cartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->where('id', $id)
            ->with(['product', 'variation'])
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
                'data' => null
            ], 404);
        }

        // Restore stock
        $product = $cartItem->product;
        $variation = $cartItem->variation;
        
        if ($variation) {
            $variation->increment('stock_quantity', $cartItem->quantity);
            if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                $variation->update(['in_stock' => true]);
            }
        } elseif ($product) {
            $product->increment('stock_quantity', $cartItem->quantity);
            if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                $product->update(['in_stock' => true]);
            }
        }

        $cartItem->delete();

        // Get updated cart info
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
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

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => [
                'cart_count' => $cartItems->count(),
                'summary' => [
                    'item_count' => $cartItems->count(),
                    'total_quantity' => $totalQuantity,
                    'original_subtotal' => number_format($originalSubtotal, 2, '.', ''),
                    'discount_amount' => number_format($discountAmountTotal, 2, '.', ''),
                    'subtotal' => number_format($cartSubtotal, 2, '.', ''),
                    'total' => number_format($cartSubtotal, 2, '.', ''),
                ],
                // Keep for backward compatibility
                'cart_total' => number_format($cartSubtotal, 2, '.', ''),
            ]
        ]);
    }

    /**
     * Get cart count
     */
    public function count(Request $request)
    {
        $customer = $this->getCustomer($request);
        $cartCount = ShoppingCartItem::where('vendor_customer_id', $customer->id)->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart count retrieved successfully',
            'data' => [
                'cart_count' => $cartCount
            ]
        ]);
    }

    /**
     * Clear all items from cart
     */
    public function clear(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->with(['product', 'variation'])
            ->get();
        
        // Restore stock for all items
        foreach ($cartItems as $cartItem) {
            if ($cartItem->variation) {
                $cartItem->variation->increment('stock_quantity', $cartItem->quantity);
                if ($cartItem->variation->fresh()->stock_quantity > 0 && !$cartItem->variation->in_stock) {
                    $cartItem->variation->update(['in_stock' => true]);
                }
            } elseif ($cartItem->product) {
                $cartItem->product->increment('stock_quantity', $cartItem->quantity);
                if ($cartItem->product->fresh()->stock_quantity > 0 && !$cartItem->product->in_stock) {
                    $cartItem->product->update(['in_stock' => true]);
                }
            }
        }
        
        ShoppingCartItem::where('vendor_customer_id', $customer->id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => [
                'items_removed' => $cartItems->count()
            ]
        ]);
    }

    /**
     * Generate proforma invoice from cart
     */
    public function generateInvoice(Request $request)
    {
        $customer = $this->getCustomer($request);
        $vendor = $customer->vendor;
        
        $cartItems = ShoppingCartItem::where('vendor_customer_id', $customer->id)
            ->with(['product', 'product.vendor', 'variation'])
            ->get()
            ->filter(function ($item) use ($customer) {
                return $item->product && $item->product->vendor_id == $customer->vendor_id;
            });

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
                'data' => null
            ], 400);
        }

        $invoiceDate = now()->format('Y-m-d');
        
        // Calculate total
        $total = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Prepare invoice data with store details
        $invoiceData = [
            'cart_items' => $cartItems->map(function ($item) {
                $itemData = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_slug' => $item->product->slug,
                    'product_description' => $item->product->description,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->price * $item->quantity,
                    'main_photo_url' => $item->product->mainPhoto?->url,
                ];
                
                if ($item->product_variation_id && $item->variation) {
                    $itemData['product_variation_id'] = $item->product_variation_id;
                    $itemData['variation_display_name'] = $item->variation->display_name;
                    $itemData['variation_attributes'] = $item->variation->formatted_attributes ?? $item->variation->attribute_values;
                    $itemData['variation_sku'] = $item->variation->sku;
                }
                
                return $itemData;
            })->values()->toArray(),
            'total' => $total,
            'invoice_date' => $invoiceDate,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'mobile_number' => $customer->mobile_number,
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'postal_code' => $customer->postal_code,
                'discount_percentage' => $customer->discount_percentage,
            ],
            'store' => [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_logo_url' => $vendor->store_logo_url,
                'store_banner_url' => $vendor->store_banner_url,
                'banner_redirect_url' => $vendor->banner_redirect_url,
                'business_name' => $vendor->business_name,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number ?? null,
            ],
        ];

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Create proforma invoice
        $proformaInvoice = ProformaInvoice::create([
            'invoice_number' => $invoiceNumber,
            'vendor_id' => $vendor->id,
            'vendor_customer_id' => $customer->id,
            'total_amount' => $total,
            'invoice_data' => $invoiceData,
            'status' => ProformaInvoice::STATUS_DRAFT,
        ]);

        // Clear the cart
        ShoppingCartItem::where('vendor_customer_id', $customer->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proforma invoice generated successfully',
            'data' => [
                'invoice' => [
                    'id' => $proformaInvoice->id,
                    'invoice_number' => $proformaInvoice->invoice_number,
                    'total_amount' => $proformaInvoice->total_amount,
                    'status' => $proformaInvoice->status,
                    'created_at' => $proformaInvoice->created_at,
                ],
                'invoice_data' => $invoiceData,
            ]
        ], 201);
    }

    /**
     * Generate a serialized invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        return \Illuminate\Support\Facades\DB::transaction(function () use ($year, $prefix) {
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
}
