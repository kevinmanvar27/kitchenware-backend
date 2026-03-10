<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ShoppingCartItem;
use App\Models\ProformaInvoice;
use App\Models\WithoutGstInvoice;
use App\Models\User;
use App\Models\Notification;
use App\Models\Coupon;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ShoppingCartController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the shopping cart.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            // Check if there are any items in the guest cart
            $sessionId = session()->getId();
            $guestCartCount = ShoppingCartItem::forSession($sessionId)->count();
            
            // If guest has items in cart, allow access to cart page
            // Otherwise, redirect to login
            if ($guestCartCount == 0) {
                return redirect()->route('login');
            }
        }
        
        if (Auth::check()) {
            $cartItems = Auth::user()->cartItems()->with(['product', 'variation'])->get();
        } else {
            // For guests, get cart items by session ID
            $sessionId = session()->getId();
            $cartItems = ShoppingCartItem::forSession($sessionId)->with(['product', 'variation'])->get();
        }
        
        $total = $cartItems->sum(function ($item) {
            // Use the price stored in the cart item, which was calculated at time of adding
            return $item->price * $item->quantity;
        });
        
        return view('frontend.cart', compact('cartItems', 'total'));
    }

    /**
     * Add a product to the shopping cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ?? 1;
        $variationId = $request->product_variation_id;
        
        // For variable products, variation is required
        if ($product->product_type === 'variable' && !$variationId) {
            return response()->json([
                'success' => false,
                'message' => 'Please select product options.'
            ]);
        }
        
        // Validate variation belongs to product
        $variation = null;
        if ($variationId) {
            $variation = ProductVariation::where('id', $variationId)
                ->where('product_id', $product->id)
                ->first();
                
            if (!$variation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid product variation selected.'
                ]);
            }
        }

        // Build where clause for checking existing cart item
        // Always include product_variation_id (even if null) to properly match cart items
        $whereClause = Auth::check() ? 
            [
                'user_id' => Auth::id(), 
                'product_id' => $product->id,
                'product_variation_id' => $variationId
            ] :
            [
                'session_id' => session()->getId(), 
                'product_id' => $product->id,
                'product_variation_id' => $variationId
            ];
        
        // Check if item already exists in cart to calculate total needed quantity
        $existingCartItem = ShoppingCartItem::where($whereClause)->first();

        // Calculate total quantity needed (existing + new)
        $existingQuantity = $existingCartItem ? $existingCartItem->quantity : 0;
        $totalQuantityNeeded = $existingQuantity + $quantity;

        // Check stock - use variation stock if variation exists, otherwise product stock
        $stockQuantity = $variation ? $variation->stock_quantity : $product->stock_quantity;
        // For variations, only check stock quantity (status is for admin control)
        $inStock = $variation ? ($variation->stock_quantity > 0) : $product->in_stock;
        
        if (!$inStock || $stockQuantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock or insufficient quantity available.'
            ]);
        }

        // Calculate discounted price using our helper function
        // Use variation price if available, otherwise product price
        if ($variation) {
            $priceToUse = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) ? 
                          $variation->selling_price : $variation->mrp;
        } else {
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) ? 
                          $product->selling_price : $product->mrp;
        }
        
        // For guests, use null user and calculate price without discount
        // For authenticated users, calculate with their discount
        $discountedPrice = $priceToUse;
        if (Auth::check()) {
            $discountedPrice = calculateDiscountedPrice($priceToUse, Auth::user());
        }

        // Prepare data for cart item - only include values that should be updated
        $cartData = [
            'quantity' => $totalQuantityNeeded,
            'price' => $discountedPrice,
        ];

        // Add user_id or session_id based on authentication status
        if (Auth::check()) {
            $cartData['user_id'] = Auth::id();
        } else {
            $cartData['session_id'] = session()->getId();
        }

        // Add or update cart item with the discounted price
        $cartItem = ShoppingCartItem::updateOrCreate($whereClause, $cartData);

        // REDUCE STOCK QUANTITY by the quantity being added (not total)
        if ($variation) {
            // Reduce variation stock
            $variation->decrement('stock_quantity', $quantity);
            
            // Update variation status if stock is depleted
            if ($variation->fresh()->stock_quantity <= 0) {
                $variation->update(['in_stock' => false]);
            }
        } else {
            // Reduce product stock
            $product->decrement('stock_quantity', $quantity);
            
            // Update in_stock status if stock is depleted
            if ($product->fresh()->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            }
        }

        // Get updated cart count
        if (Auth::check()) {
            $cartCount = Auth::user()->cartItems()->count();
        } else {
            $cartCount = ShoppingCartItem::forSession(session()->getId())->count();
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart_count' => $cartCount,
        ]);
    }

    /**
     * Update the quantity of a cart item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCart(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Find cart item based on authentication status
        if (Auth::check()) {
            $cartItem = ShoppingCartItem::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        } else {
            $cartItem = ShoppingCartItem::where('session_id', session()->getId())->where('id', $id)->firstOrFail();
        }
        
        $product = $cartItem->product;
        $variation = $cartItem->variation;
        $oldQuantity = $cartItem->quantity;
        $newQuantity = $request->quantity;
        $quantityDifference = $newQuantity - $oldQuantity;

        // Determine which stock to check (variation or product)
        $stockQuantity = $variation ? $variation->stock_quantity : $product->stock_quantity;
        
        // If increasing quantity, check if enough stock is available
        if ($quantityDifference > 0) {
            if ($stockQuantity < $quantityDifference) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is out of stock or insufficient quantity available. Only ' . $stockQuantity . ' more available.'
                ]);
            }
            // Reduce stock by the difference
            if ($variation) {
                $variation->decrement('stock_quantity', $quantityDifference);
            } else {
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

        // Update in_stock status based on new stock quantity
        if ($variation) {
            if ($variation->fresh()->stock_quantity <= 0) {
                $variation->update(['in_stock' => false]);
            } else {
                $variation->update(['in_stock' => true]);
            }
        } else {
            $product->refresh();
            if ($product->stock_quantity <= 0) {
                $product->update(['in_stock' => false]);
            } elseif ($product->stock_quantity > 0 && !$product->in_stock) {
                $product->update(['in_stock' => true]);
            }
        }

        $cartItem->update([
            'quantity' => $newQuantity,
        ]);

        // Calculate item total and cart total
        $itemTotal = $cartItem->price * $cartItem->quantity;
        
        if (Auth::check()) {
            $cartItems = Auth::user()->cartItems()->get();
        } else {
            $cartItems = ShoppingCartItem::forSession(session()->getId())->get();
        }
        
        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'item_total' => number_format($itemTotal, 2, '.', ''),
            'cart_total' => number_format($cartTotal, 2, '.', ''),
        ]);
    }

    /**
     * Remove an item from the cart.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCart($id)
    {
        // Find cart item based on authentication status
        if (Auth::check()) {
            $cartItem = ShoppingCartItem::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        } else {
            $cartItem = ShoppingCartItem::where('session_id', session()->getId())->where('id', $id)->firstOrFail();
        }
        
        // RESTORE STOCK QUANTITY before deleting the cart item
        $product = $cartItem->product;
        $variation = $cartItem->variation;
        
        if ($variation) {
            // Restore variation stock
            $variation->increment('stock_quantity', $cartItem->quantity);
            
            // Update variation status if stock was restored
            if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                $variation->update(['in_stock' => true]);
            }
        } elseif ($product) {
            // Restore product stock
            $product->increment('stock_quantity', $cartItem->quantity);
            
            // Update in_stock status if stock was restored
            if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                $product->update(['in_stock' => true]);
            }
        }
        
        $cartItem->delete();

        // Get updated cart count and total
        if (Auth::check()) {
            $cartCount = Auth::user()->cartItems()->count();
            $cartItems = Auth::user()->cartItems()->get();
        } else {
            $cartCount = ShoppingCartItem::forSession(session()->getId())->count();
            $cartItems = ShoppingCartItem::forSession(session()->getId())->get();
        }
        
        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart!',
            'cart_count' => $cartCount,
            'cart_total' => number_format($cartTotal, 2, '.', ''),
        ]);
    }

    /**
     * Get the cart count for the current user or session.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartCount()
    {
        if (Auth::check()) {
            $cartCount = Auth::user()->cartItems()->count();
        } else {
            $cartCount = ShoppingCartItem::forSession(session()->getId())->count();
        }
        
        return response()->json(['cart_count' => $cartCount]);
    }
    
    /**
     * Migrate guest cart items to authenticated user's cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function migrateGuestCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.']);
        }
        
        $userId = Auth::id();
        
        // First, migrate database guest cart items (items added when session was active)
        $this->migrateDatabaseGuestCart($userId);
        
        // Then, migrate localStorage guest cart items (items added before session)
        $this->migrateLocalStorageGuestCart($request, $userId);
        
        return response()->json(['success' => true, 'message' => 'Cart items migrated successfully.']);
    }
    
    /**
     * Migrate database guest cart items to authenticated user's cart.
     *
     * @param  int  $userId
     * @return void
     */
    private function migrateDatabaseGuestCart($userId)
    {
        $sessionId = session()->getId();
        
        // Get guest cart items from database
        $guestCartItems = ShoppingCartItem::forSession($sessionId)->get();
        
        // Migrate each guest cart item to user's cart
        foreach ($guestCartItems as $guestCartItem) {
            // Check if user already has this product (with same variation) in their cart
            $existingCartItem = ShoppingCartItem::where('user_id', $userId)
                ->where('product_id', $guestCartItem->product_id)
                ->where('product_variation_id', $guestCartItem->product_variation_id)
                ->first();
                
            if ($existingCartItem) {
                // If user already has this product/variation, update quantity (combine quantities)
                // Make sure we don't exceed stock quantity
                $stockQuantity = $guestCartItem->variation 
                    ? $guestCartItem->variation->stock_quantity 
                    : $guestCartItem->product->stock_quantity;
                $newQuantity = min($existingCartItem->quantity + $guestCartItem->quantity, $stockQuantity);
                $existingCartItem->update([
                    'quantity' => $newQuantity
                ]);
                
                // Delete the guest cart item
                $guestCartItem->delete();
            } else {
                // If user doesn't have this product/variation, transfer the guest cart item to user
                // Make sure we don't exceed stock quantity
                $stockQuantity = $guestCartItem->variation 
                    ? $guestCartItem->variation->stock_quantity 
                    : $guestCartItem->product->stock_quantity;
                $newQuantity = min($guestCartItem->quantity, $stockQuantity);
                $guestCartItem->update([
                    'user_id' => $userId,
                    'session_id' => null,
                    'quantity' => $newQuantity
                ]);
            }
        }
    }
    
    /**
     * Migrate localStorage guest cart items to authenticated user's cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return void
     */
    private function migrateLocalStorageGuestCart(Request $request, $userId)
    {
        // Get guest cart data from request
        $localStorageCart = $request->get('guest_cart', '[]');
        
        // Decode the cart data
        $guestCartItems = json_decode($localStorageCart, true);
        
        // If we don't have valid cart data, return
        if (!is_array($guestCartItems)) {
            return;
        }
        
        // Process each item in the localStorage cart
        foreach ($guestCartItems as $guestCartItem) {
            // Validate the cart item data
            if (!isset($guestCartItem['product_id']) || !isset($guestCartItem['quantity'])) {
                continue;
            }
            
            $productId = $guestCartItem['product_id'];
            $quantity = (int) $guestCartItem['quantity'];
            $variationId = $guestCartItem['product_variation_id'] ?? null;
            
            // Validate product exists
            $product = Product::find($productId);
            if (!$product) {
                continue;
            }
            
            // Check variation if specified
            $variation = null;
            if ($variationId) {
                $variation = ProductVariation::where('id', $variationId)
                    ->where('product_id', $productId)
                    ->first();
                if (!$variation) {
                    continue; // Skip if variation doesn't exist
                }
            }
            
            // Check stock availability
            $stockQuantity = $variation ? $variation->stock_quantity : $product->stock_quantity;
            $inStock = $variation ? ($variation->stock_quantity > 0) : $product->in_stock;
            
            if (!$inStock || $stockQuantity < $quantity) {
                continue;
            }
            
            // Calculate the price for this product/variation
            if ($variation) {
                $priceToUse = (!is_null($variation->selling_price) && $variation->selling_price !== '' && $variation->selling_price >= 0) ? 
                              $variation->selling_price : $variation->mrp;
            } else {
                $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) ? 
                              $product->selling_price : $product->mrp;
            }
            
            // Calculate price with user discount
            $user = User::find($userId);
            $discountedPrice = $user ? calculateDiscountedPrice($priceToUse, $user) : $priceToUse;
            
            // Check if user already has this product/variation in their cart
            $existingCartItem = ShoppingCartItem::where('user_id', $userId)
                ->where('product_id', $productId)
                ->where('product_variation_id', $variationId)
                ->first();
                
            if ($existingCartItem) {
                // If user already has this product/variation, update quantity (combine quantities)
                $newQuantity = min($existingCartItem->quantity + $quantity, $stockQuantity);
                $existingCartItem->update([
                    'quantity' => $newQuantity
                ]);
            } else {
                // If user doesn't have this product/variation, create a new cart item
                $newQuantity = min($quantity, $stockQuantity);
                ShoppingCartItem::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'product_variation_id' => $variationId,
                    'quantity' => $newQuantity,
                    'price' => $discountedPrice
                ]);
            }
        }
    }
    
    /**
     * Generate and display the proforma invoice.
     *
     * @return \Illuminate\View\View
     */
    public function generateProformaInvoice()
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return redirect()->route('login');
        }
        
        if (Auth::check()) {
            $cartItems = Auth::user()->cartItems()->with(['product', 'product.vendor', 'variation'])->get();
        } else {
            // For guests, get cart items by session ID
            $sessionId = session()->getId();
            $cartItems = ShoppingCartItem::forSession($sessionId)->with(['product', 'product.vendor', 'variation'])->get();
        }
        
        $subtotal = $cartItems->sum(function ($item) {
            // Use the price stored in the cart item, which was calculated at time of adding
            return $item->price * $item->quantity;
        });
        
        // Check for applied coupon
        $appliedCoupon = session('applied_coupon');
        $couponDiscount = 0;
        $couponData = null;
        $couponToRecord = null;
        
        if ($appliedCoupon) {
            $coupon = Coupon::find($appliedCoupon['id']);
            
            // Verify coupon is still valid
            if ($coupon && $coupon->isValid() && $coupon->canBeUsedBy(Auth::user())) {
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
        
        // Generate invoice date
        $invoiceDate = now()->format('Y-m-d');
        
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
            
            // Calculate proportional coupon discount for this vendor
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
            
            // Prepare invoice data for this vendor
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
                'customer' => Auth::check() ? [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'address' => Auth::user()->address,
                    'mobile_number' => Auth::user()->mobile_number
                ] : null,
                'session_id' => Auth::check() ? null : session()->getId()
            ];
            
            // Save the proforma invoice to the database with retry logic for duplicate invoice numbers
            $proformaInvoice = $this->createProformaInvoiceWithRetry($vendorTotal, $invoiceData, $vendorId > 0 ? $vendorId : null);
            $createdInvoices[] = $proformaInvoice;
            
            // Store vendor info for notifications
            if ($vendorId > 0) {
                $vendorNotifications[$vendorId] = [
                    'invoice' => $proformaInvoice,
                    'vendor_id' => $vendorId
                ];
                
                // Add the user as a customer of this vendor (if authenticated)
                if (Auth::check()) {
                    \App\Models\VendorCustomer::addCustomerToVendor($vendorId, Auth::id(), $proformaInvoice->id);
                }
            }
        }
        
        // Use the first invoice for coupon recording and main notification
        $mainInvoice = $createdInvoices[0] ?? null;
        $invoiceNumber = $mainInvoice ? $mainInvoice->invoice_number : 'N/A';
        
        // Record coupon usage after invoice is created (so we can link to invoice)
        if ($couponToRecord && $couponDiscount > 0 && $mainInvoice) {
            $couponToRecord->recordUsage(Auth::user(), $couponDiscount, $mainInvoice->id);
        }
        
        // Create database notifications for admin users
        $adminUsers = User::where('user_role', 'admin')->orWhere('user_role', 'super_admin')->get();
        foreach ($adminUsers as $adminUser) {
            Notification::create([
                'user_id' => $adminUser->id,
                'title' => 'New Proforma Invoice Created',
                'message' => 'A new proforma invoice #' . $invoiceNumber . ' has been created by ' . (Auth::check() ? Auth::user()->name : 'Guest'),
                'type' => 'proforma_invoice',
                'data' => json_encode([
                    'invoice_id' => $mainInvoice ? $mainInvoice->id : null,
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => Auth::check() ? Auth::user()->name : 'Guest',
                    'customer_avatar' => Auth::check() ? (Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : null) : null
                ]),
                'read' => false,
            ]);
        }
        
        // Send push notifications to admin users who have device tokens
        if (Auth::check()) {
            foreach ($adminUsers as $adminUser) {
                if (!empty($adminUser->device_token)) {
                    $payload = [
                        'notification' => [
                            'title' => 'New Proforma Invoice Created',
                            'body' => 'A new proforma invoice #' . $invoiceNumber . ' has been created by ' . Auth::user()->name
                        ],
                        'data' => [
                            'invoice_id' => $mainInvoice ? $mainInvoice->id : null,
                            'invoice_number' => $invoiceNumber,
                            'type' => 'proforma_invoice_created'
                        ]
                    ];
                    
                    $this->notificationService->sendPushNotification($adminUser->device_token, $payload);
                }
            }
        }
        
        // Send notifications to vendors for their respective invoices
        foreach ($vendorNotifications as $vendorId => $data) {
            $vendor = \App\Models\Vendor::find($vendorId);
            if ($vendor && $vendor->user) {
                $vendorInvoice = $data['invoice'];
                
                // Create database notification for vendor
                Notification::create([
                    'user_id' => $vendor->user_id,
                    'title' => 'New Order Invoice Received',
                    'message' => 'A new proforma invoice #' . $vendorInvoice->invoice_number . ' has been created for your store by ' . (Auth::check() ? Auth::user()->name : 'Guest'),
                    'type' => 'vendor_proforma_invoice',
                    'data' => json_encode([
                        'invoice_id' => $vendorInvoice->id,
                        'invoice_number' => $vendorInvoice->invoice_number,
                        'customer_name' => Auth::check() ? Auth::user()->name : 'Guest',
                        'customer_avatar' => Auth::check() ? (Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : null) : null,
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
        
        // Clear the cart after generating the proforma invoice
        if (Auth::check()) {
            // For authenticated users, delete all cart items for the user
            ShoppingCartItem::where('user_id', Auth::id())->delete();
        } else {
            // For guests, delete all cart items for the session
            ShoppingCartItem::where('session_id', session()->getId())->delete();
        }
        
        // Clear the applied coupon from session
        session()->forget('applied_coupon');
        
        // Redirect to the invoices list instead of showing the proforma invoice page
        return redirect()->route('frontend.cart.proforma.invoices')->with('success', 'Proforma invoice generated successfully! Cart has been emptied.');
    }
    
    /**
     * Display a listing of the user's proforma invoices.
     *
     * @return \Illuminate\View\View
     */
    public function listProformaInvoices()
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return redirect()->route('login');
        }
        
        // Get all proforma invoices for the authenticated user
        if (Auth::check()) {
            $proformaInvoices = ProformaInvoice::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // For guests, get invoices by session ID
            $sessionId = session()->getId();
            $proformaInvoices = ProformaInvoice::where('session_id', $sessionId)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('frontend.proforma-invoice-list', compact('proformaInvoices'));
    }
    
    /**
     * Get the details of a specific proforma invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProformaInvoiceDetails($id)
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Find the proforma invoice with vendor relationship
        if (Auth::check()) {
            $proformaInvoice = ProformaInvoice::with('vendor')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        } else {
            // For guests, get invoice by session ID
            $sessionId = session()->getId();
            $proformaInvoice = ProformaInvoice::with('vendor')
                ->where('id', $id)
                ->where('session_id', $sessionId)
                ->first();
        }
        
        if (!$proformaInvoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }
        
        // Get the invoice data (handle both array and JSON string for backward compatibility)
        $invoiceData = $proformaInvoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded from old records)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Add vendor/store details to the response
        $storeDetails = null;
        if ($proformaInvoice->vendor) {
            $vendor = $proformaInvoice->vendor;
            $storeDetails = [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_logo' => $vendor->store_logo_url,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'country' => $vendor->country,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number,
                'full_address' => implode(', ', array_filter([
                    $vendor->business_address,
                    $vendor->city,
                    $vendor->state,
                    $vendor->postal_code,
                    $vendor->country
                ])),
            ];
        }
        
        // Automatically remove all notifications for this invoice when viewing directly
        $unreadCount = 0;
        if (Auth::check()) {
            // Get all unread notifications for the current user that are related to this invoice
            $notifications = \App\Models\Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->where('type', 'proforma_invoice')
                ->where('data', 'like', '%"invoice_id":' . $id . '%')
                ->get();
            
            // Delete all matching notifications
            foreach ($notifications as $notification) {
                $notification->delete();
            }
            
            // Get updated unread count
            $unreadCount = \App\Models\Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count();
        }
        
        return response()->json([
            'invoice' => $proformaInvoice,
            'data' => $invoiceData,
            'store' => $storeDetails,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Add products from a proforma invoice back to the cart.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addInvoiceToCart($id)
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return redirect()->route('login');
        }
        
        // Find the proforma invoice
        if (Auth::check()) {
            $proformaInvoice = ProformaInvoice::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        } else {
            // For guests, get invoice by session ID
            $sessionId = session()->getId();
            $proformaInvoice = ProformaInvoice::where('id', $id)
                ->where('session_id', $sessionId)
                ->first();
        }
        
        if (!$proformaInvoice) {
            return redirect()->route('frontend.cart.proforma.invoices')->with('error', 'Invoice not found.');
        }
        
        // Check if the invoice is in draft status
        if ($proformaInvoice->status !== ProformaInvoice::STATUS_DRAFT) {
            return redirect()->route('frontend.cart.proforma.invoices')->with('error', 'Only draft invoices can be added to cart.');
        }
        
        // Get the invoice data (handle both array and JSON string for backward compatibility)
        $invoiceData = $proformaInvoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded from old records)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Add each item from the invoice to the cart
        // NOTE: Stock was already reduced when invoice was created
        // We need to handle merging with existing cart items carefully
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                // Check if product still exists
                $product = Product::find($item['product_id']);
                if (!$product) {
                    continue; // Skip if product no longer exists
                }
                
                // Build where clause for checking existing cart item
                // Always include product_variation_id (even if null) to properly match cart items
                $variationId = $item['product_variation_id'] ?? null;
                $whereClause = Auth::check() ? 
                    [
                        'user_id' => Auth::id(), 
                        'product_id' => $item['product_id'],
                        'product_variation_id' => $variationId
                    ] :
                    [
                        'session_id' => session()->getId(), 
                        'product_id' => $item['product_id'],
                        'product_variation_id' => $variationId
                    ];
                
                // Check if item already exists in cart
                $existingCartItem = ShoppingCartItem::where($whereClause)->first();
                
                if ($existingCartItem) {
                    // Item already in cart - need to merge quantities
                    // The invoice items' stock was already reduced, so just add quantity
                    $newQuantity = $existingCartItem->quantity + $item['quantity'];
                    $existingCartItem->update([
                        'quantity' => $newQuantity,
                        'price' => $item['price']
                    ]);
                } else {
                    // Item not in cart - create new cart item
                    // Stock was already reduced when invoice was created, no need to reduce again
                    $cartData = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ];
                    
                    // Add variation_id if present
                    if ($variationId) {
                        $cartData['product_variation_id'] = $variationId;
                    }
                    
                    // Add user_id or session_id based on authentication status
                    if (Auth::check()) {
                        $cartData['user_id'] = Auth::id();
                    } else {
                        $cartData['session_id'] = session()->getId();
                    }
                    
                    ShoppingCartItem::create($cartData);
                }
            }
        }
        
        // Delete the proforma invoice after adding items to cart
        // NOTE: We don't restore stock here because items are moving to cart (stock stays reduced)
        $proformaInvoice->delete();
        
        return redirect()->route('frontend.cart.index')->with('success', 'Products from proforma invoice added to cart successfully! The proforma invoice has been removed.');
    }
    
    /**
     * Delete a proforma invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProformaInvoice($id)
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return redirect()->route('login');
        }
        
        // Find the proforma invoice
        if (Auth::check()) {
            $proformaInvoice = ProformaInvoice::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        } else {
            // For guests, get invoice by session ID
            $sessionId = session()->getId();
            $proformaInvoice = ProformaInvoice::where('id', $id)
                ->where('session_id', $sessionId)
                ->first();
        }
        
        if (!$proformaInvoice) {
            return redirect()->route('frontend.cart.proforma.invoices')->with('error', 'Invoice not found.');
        }
        
        // RESTORE STOCK for all items in the invoice before deleting
        $invoiceData = $proformaInvoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                // Check if this is a variable product with variation
                if (!empty($item['product_variation_id'])) {
                    // Restore variation stock
                    $variation = ProductVariation::find($item['product_variation_id']);
                    if ($variation) {
                        $variation->increment('stock_quantity', $item['quantity']);
                        
                        // Update variation in_stock status if stock was restored
                        if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                            $variation->update(['in_stock' => true]);
                        }
                    }
                } else {
                    // Restore simple product stock
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->increment('stock_quantity', $item['quantity']);
                        
                        // Update in_stock status if stock was restored
                        if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                            $product->update(['in_stock' => true]);
                        }
                    }
                }
            }
        }
        
        // Delete the proforma invoice
        $proformaInvoice->delete();
        
        return redirect()->route('frontend.cart.proforma.invoices')->with('success', 'Proforma invoice deleted and stock restored successfully!');
    }
    
    /**
     * Generate a serialized invoice number with database locking to prevent duplicates.
     *
     * @return string
     */
    private function generateInvoiceNumber()
    {
        // Get the current year
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
                // Extract the sequence number from the latest invoice
                $latestNumber = $latestInvoice->invoice_number;
                $parts = explode('-', $latestNumber);
                
                // If the latest invoice is from the current year, increment the sequence
                if (count($parts) >= 3 && $parts[1] == $year) {
                    $sequence = (int)$parts[2] + 1;
                } else {
                    // If it's a new year or no previous invoices, start from 1
                    $sequence = 1;
                }
            } else {
                // If no previous invoices, start from 1
                $sequence = 1;
            }
            
            // Format the sequence number with leading zeros (e.g., 0001)
            $sequenceFormatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);
            
            // Return the formatted invoice number (e.g., INV-2025-0001)
            return "INV-{$year}-{$sequenceFormatted}";
        });
    }
    
    /**
     * Create a proforma invoice with retry logic to handle duplicate invoice numbers.
     *
     * @param  float  $total
     * @param  array  $invoiceData
     * @param  int|null  $vendorId
     * @param  int  $maxRetries
     * @return \App\Models\ProformaInvoice
     * @throws \Exception
     */
    private function createProformaInvoiceWithRetry($total, $invoiceData, $vendorId = null, $maxRetries = 5)
    {
        $attempts = 0;
        $lastException = null;
        
        while ($attempts < $maxRetries) {
            try {
                return \Illuminate\Support\Facades\DB::transaction(function () use ($total, $invoiceData, $vendorId) {
                    // Generate invoice number inside the transaction
                    $invoiceNumber = $this->generateInvoiceNumber();
                    
                    // Create the proforma invoice
                    return ProformaInvoice::create([
                        'invoice_number' => $invoiceNumber,
                        'user_id' => Auth::check() ? Auth::id() : null,
                        'vendor_id' => $vendorId,
                        'session_id' => Auth::check() ? null : session()->getId(),
                        'total_amount' => $total,
                        'invoice_data' => $invoiceData,
                        'status' => ProformaInvoice::STATUS_DRAFT,
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
     * Generate and download PDF for a proforma invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadProformaInvoicePDF($id)
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return redirect()->route('login');
        }
        
        // Find the proforma invoice with vendor relationship
        if (Auth::check()) {
            $proformaInvoice = ProformaInvoice::with('vendor')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        } else {
            // For guests, get invoice by session ID
            $sessionId = session()->getId();
            $proformaInvoice = ProformaInvoice::with('vendor')
                ->where('id', $id)
                ->where('session_id', $sessionId)
                ->first();
        }
        
        if (!$proformaInvoice) {
            return redirect()->route('frontend.cart.proforma.invoices')->with('error', 'Invoice not found.');
        }
        
        // Get the invoice data (handle both array and JSON string for backward compatibility)
        $invoiceData = $proformaInvoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded from old records)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Prepare store/vendor details
        $storeDetails = null;
        if ($proformaInvoice->vendor) {
            $vendor = $proformaInvoice->vendor;
            
            // Determine the correct logo path for PDF (needs file path, not URL)
            $logoPath = null;
            if ($vendor->store_logo) {
                // Check in vendor root folder
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $vendor->store_logo)) {
                    $logoPath = 'vendor/' . $vendor->store_logo;
                }
                // Check in vendor-specific subfolder
                elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $vendor->id . '/' . $vendor->store_logo)) {
                    $logoPath = 'vendor/' . $vendor->id . '/' . $vendor->store_logo;
                }
            }
            
            $storeDetails = [
                'store_name' => $vendor->store_name,
                'store_logo' => $logoPath,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'country' => $vendor->country,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number,
                'full_address' => implode(', ', array_filter([
                    $vendor->business_address,
                    $vendor->city,
                    $vendor->state,
                    $vendor->postal_code,
                    $vendor->country
                ])),
            ];
        }
        
        // Prepare data for the PDF view
        $data = [
            'invoice' => $proformaInvoice,
            'invoiceData' => $invoiceData,
            'store' => $storeDetails,
            'siteTitle' => setting('site_title', 'Frontend App'),
            'companyAddress' => setting('address', 'Company Address'),
            'companyEmail' => setting('email', 'company@example.com'),
            'companyPhone' => setting('phone', '+1 (555) 123-4567')
        ];
        
        // Load the PDF view
        $pdf = Pdf::loadView('frontend.proforma-invoice-pdf', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Download the PDF with a meaningful filename
        // If invoice is approved, name it as 'invoice' instead of 'proforma-invoice'
        $filePrefix = ($proformaInvoice->status === \App\Models\ProformaInvoice::STATUS_APPROVED) 
            ? 'invoice' 
            : 'proforma-invoice';
        return $pdf->download($filePrefix . '-' . $proformaInvoice->invoice_number . '.pdf');
    }
    
    /**
     * List all without-GST invoices for the authenticated user.
     *
     * @return \Illuminate\View\View
     */
    public function listWithoutGstInvoices()
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return redirect()->route('login');
        }
        
        // Get all without-GST invoices for the authenticated user
        if (Auth::check()) {
            $withoutGstInvoices = WithoutGstInvoice::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // For guests, get invoices by session ID
            $sessionId = session()->getId();
            $withoutGstInvoices = WithoutGstInvoice::where('session_id', $sessionId)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('frontend.without-gst-invoice-list', compact('withoutGstInvoices'));
    }
    
    /**
     * Get the details of a specific without-GST invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithoutGstInvoiceDetails($id)
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Find the without-GST invoice with vendor relationship
        if (Auth::check()) {
            $invoice = WithoutGstInvoice::with('vendor')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        } else {
            // For guests, get invoice by session ID
            $sessionId = session()->getId();
            $invoice = WithoutGstInvoice::with('vendor')
                ->where('id', $id)
                ->where('session_id', $sessionId)
                ->first();
        }
        
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }
        
        // Get the invoice data (handle both array and JSON string for backward compatibility)
        $invoiceData = $invoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded from old records)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Add vendor/store details to the response
        $storeDetails = null;
        if ($invoice->vendor) {
            $vendor = $invoice->vendor;
            $storeDetails = [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_logo' => $vendor->store_logo_url,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'country' => $vendor->country,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number,
                'full_address' => implode(', ', array_filter([
                    $vendor->business_address,
                    $vendor->city,
                    $vendor->state,
                    $vendor->postal_code,
                    $vendor->country
                ])),
            ];
        }
        
        return response()->json([
            'invoice' => $invoice,
            'data' => $invoiceData,
            'store' => $storeDetails
        ]);
    }
    
    /**
     * Generate and download PDF for a without-GST invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadWithoutGstInvoicePDF($id)
    {
        // Check if frontend requires authentication and user is not logged in
        $setting = \App\Models\Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For registered_users_only and admin_approval_required modes, 
        // redirect guests from cart pages to login, unless it's open_for_all
        if ($accessPermission !== 'open_for_all' && !Auth::check()) {
            return redirect()->route('login');
        }
        
        // Find the without-GST invoice with vendor relationship
        if (Auth::check()) {
            $invoice = WithoutGstInvoice::with('vendor')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
        } else {
            // For guests, get invoice by session ID
            $sessionId = session()->getId();
            $invoice = WithoutGstInvoice::with('vendor')
                ->where('id', $id)
                ->where('session_id', $sessionId)
                ->first();
        }
        
        if (!$invoice) {
            return redirect()->route('frontend.cart.without-gst.invoices')->with('error', 'Invoice not found.');
        }
        
        // Get the invoice data (handle both array and JSON string for backward compatibility)
        $invoiceData = $invoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded from old records)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Prepare store/vendor details
        $storeDetails = null;
        if ($invoice->vendor) {
            $vendor = $invoice->vendor;
            
            // Determine the correct logo path for PDF (needs file path, not URL)
            $logoPath = null;
            if ($vendor->store_logo) {
                // Check in vendor root folder
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $vendor->store_logo)) {
                    $logoPath = 'vendor/' . $vendor->store_logo;
                }
                // Check in vendor-specific subfolder
                elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $vendor->id . '/' . $vendor->store_logo)) {
                    $logoPath = 'vendor/' . $vendor->id . '/' . $vendor->store_logo;
                }
            }
            
            $storeDetails = [
                'store_name' => $vendor->store_name,
                'store_logo' => $logoPath,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'country' => $vendor->country,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number,
                'full_address' => implode(', ', array_filter([
                    $vendor->business_address,
                    $vendor->city,
                    $vendor->state,
                    $vendor->postal_code,
                    $vendor->country
                ])),
            ];
        }
        
        // Prepare data for the PDF view
        $data = [
            'invoice' => $invoice,
            'invoiceData' => $invoiceData,
            'store' => $storeDetails,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d'),
            'customer' => $invoiceData['customer'] ?? null,
            'cartItems' => $invoiceData['cart_items'] ?? $invoiceData['items'] ?? [],
            'total' => $invoice->total_amount,
            'siteTitle' => setting('site_title', 'Frontend App'),
            'companyAddress' => setting('address', 'Company Address'),
            'companyEmail' => setting('email', 'company@example.com'),
            'companyPhone' => setting('phone', '+1 (555) 123-4567'),
            'headerLogo' => setting('header_logo', null),
        ];
        
        // Load the PDF view
        $pdf = Pdf::loadView('frontend.without-gst-invoice-pdf', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Download the PDF with a meaningful filename
        return $pdf->download('without-gst-invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Apply a coupon code to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($request->coupon_code));
        
        // Find the coupon
        $coupon = Coupon::where('code', $code)->first();
        
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code. Please check and try again.'
            ], 400);
        }

        // Check if coupon is valid (active, not expired, not exhausted)
        if (!$coupon->isValid()) {
            $status = $coupon->status;
            $message = match($status) {
                'inactive' => 'This coupon is currently inactive.',
                'expired' => 'This coupon has expired.',
                'scheduled' => 'This coupon is not yet active.',
                'exhausted' => 'This coupon has reached its usage limit.',
                default => 'This coupon is not valid.'
            };
            
            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        // Check per-user limit for authenticated users
        $user = Auth::user();
        if (!$coupon->canBeUsedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this coupon the maximum number of times.'
            ], 400);
        }

        // Get cart total
        if (Auth::check()) {
            $cartItems = Auth::user()->cartItems()->with('product')->get();
        } else {
            $sessionId = session()->getId();
            $cartItems = ShoppingCartItem::forSession($sessionId)->with('product')->get();
        }

        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 400);
        }

        // Check minimum order amount
        if ($coupon->min_order_amount && $cartTotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order amount of ₹' . number_format($coupon->min_order_amount, 2) . ' required to use this coupon.'
            ], 400);
        }

        // Calculate discount
        $discount = $coupon->calculateDiscount($cartTotal);
        $newTotal = $cartTotal - $discount;

        // Store coupon in session
        session([
            'applied_coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_amount' => $discount,
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_amount' => $discount,
                'discount_display' => $coupon->discount_type === 'percentage' 
                    ? $coupon->discount_value . '% off' 
                    : '₹' . number_format($coupon->discount_value, 2) . ' off',
            ],
            'cart_subtotal' => number_format($cartTotal, 2),
            'cart_total' => number_format($newTotal, 2),
        ]);
    }

    /**
     * Remove the applied coupon from the cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeCoupon()
    {
        // Remove coupon from session
        session()->forget('applied_coupon');

        // Get cart total
        if (Auth::check()) {
            $cartItems = Auth::user()->cartItems()->with('product')->get();
        } else {
            $sessionId = session()->getId();
            $cartItems = ShoppingCartItem::forSession($sessionId)->with('product')->get();
        }

        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully.',
            'cart_total' => number_format($cartTotal, 2),
        ]);
    }

    /**
     * Get the currently applied coupon details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAppliedCoupon()
    {
        $appliedCoupon = session('applied_coupon');
        
        if (!$appliedCoupon) {
            return response()->json([
                'success' => true,
                'coupon' => null,
            ]);
        }

        // Verify the coupon is still valid
        $coupon = Coupon::find($appliedCoupon['id']);
        
        if (!$coupon || !$coupon->isValid()) {
            session()->forget('applied_coupon');
            return response()->json([
                'success' => true,
                'coupon' => null,
                'message' => 'The previously applied coupon is no longer valid.',
            ]);
        }

        // Recalculate discount based on current cart
        if (Auth::check()) {
            $cartItems = Auth::user()->cartItems()->with('product')->get();
        } else {
            $sessionId = session()->getId();
            $cartItems = ShoppingCartItem::forSession($sessionId)->with('product')->get();
        }

        $cartTotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Check if cart still meets minimum order requirement
        if ($coupon->min_order_amount && $cartTotal < $coupon->min_order_amount) {
            session()->forget('applied_coupon');
            return response()->json([
                'success' => true,
                'coupon' => null,
                'message' => 'Cart no longer meets the minimum order amount for the coupon.',
            ]);
        }

        $discount = $coupon->calculateDiscount($cartTotal);

        // Update session with new discount amount
        session([
            'applied_coupon' => array_merge($appliedCoupon, ['discount_amount' => $discount])
        ]);

        return response()->json([
            'success' => true,
            'coupon' => [
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_amount' => $discount,
                'discount_display' => $coupon->discount_type === 'percentage' 
                    ? $coupon->discount_value . '% off' 
                    : '₹' . number_format($coupon->discount_value, 2) . ' off',
            ],
        ]);
    }

}
