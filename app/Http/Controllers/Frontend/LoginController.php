<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\ShoppingCartItem;
use App\Models\Product;
use App\Models\ProductVariation;

class LoginController extends Controller
{
    /**
     * Show the login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Get the frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        return view('frontend.auth.login', compact('accessPermission'));
    }

    /**
     * Handle login request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Get the frontend access permission setting
            $setting = Setting::first();
            $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
            
            // If admin approval is required, check if user is approved
            if ($accessPermission === 'admin_approval_required') {
                $user = Auth::user();
                if (!$user->is_approved) {
                    // Log the user out and show pending approval message
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return back()->withErrors([
                        'email' => $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.',
                    ]);
                }
            }
            
            // Migrate guest cart items to user's cart
            $this->migrateGuestCart($request);
            
            // Check user role and redirect accordingly
            $user = Auth::user();
            
            // If user has 'user' role, redirect to frontend home
            if ($user->hasRole('user')) {
                return redirect()->intended(route('frontend.home'))->with('login_success', true);
            }
            
            // For all other roles (super_admin, admin, etc.), redirect to admin dashboard
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    
    /**
     * Migrate guest cart items to authenticated user's cart after login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    private function migrateGuestCart(Request $request)
    {
        if (!Auth::check()) {
            return;
        }
        
        $userId = Auth::id();
        
        // First, migrate database guest cart items (items added when session was active)
        $this->migrateDatabaseGuestCart($userId);
        
        // Then, migrate localStorage guest cart items (items added before session)
        $this->migrateLocalStorageGuestCart($request, $userId);
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
        // Get guest cart data from localStorage (passed via form submission)
        $localStorageCart = $request->get('guest_cart', '[]');
        
        // If we don't have localStorage data in the request, try to get it from session
        // This happens when the login form is submitted via AJAX or when we store it in session
        if ($localStorageCart === '[]' && $request->session()->has('guest_cart')) {
            $localStorageCart = $request->session()->get('guest_cart');
        }
        
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
            
            $discountedPrice = calculateDiscountedPrice($priceToUse, Auth::user());
            
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
                ShoppingCartItem::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'product_variation_id' => $variationId,
                    'quantity' => min($quantity, $stockQuantity),
                    'price' => $discountedPrice
                ]);
            }
        }
        
        // Clear the localStorage cart data from session if it exists
        $request->session()->forget('guest_cart');
    }
}