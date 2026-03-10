<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Setting;
use App\Models\ShoppingCartItem;
use App\Models\Product;
use App\Models\ProductVariation;

class UnifiedLoginController extends Controller
{
    /**
     * Admin allowed roles that can access the admin panel.
     * 
     * @var array
     */
    protected $adminRoles = [
        'super_admin',
        'admin',
        'editor',
        'staff',
    ];

    /**
     * Show the unified login form
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        // If user is already authenticated, redirect based on role
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        
        // Get the frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        return view('auth.login', compact('accessPermission'));
    }

    /**
     * Handle unified login request
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
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if the authenticated user is a User model
            if (!($user instanceof User)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Invalid account type. Please contact support.',
                ])->withInput($request->only('email', 'remember'));
            }
            
            // Route based on user role
            return $this->handleUserLogin($request, $user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Handle login based on user role
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleUserLogin(Request $request, User $user)
    {
        $role = $user->user_role;

        // Handle Admin roles (super_admin, admin, editor, staff)
        if (in_array($role, $this->adminRoles)) {
            return $this->handleAdminLogin($request, $user);
        }

        // Handle Vendor role
        if ($role === 'vendor') {
            return $this->handleVendorLogin($request, $user);
        }

        // Handle Vendor Staff role
        if ($role === 'vendor_staff') {
            return $this->handleVendorStaffLogin($request, $user);
        }

        // Handle regular user role
        if ($role === 'user') {
            return $this->handleFrontendUserLogin($request, $user);
        }

        // Unknown role - logout and show error
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return back()->withErrors([
            'email' => 'Your account type is not recognized. Please contact support.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Handle admin login
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleAdminLogin(Request $request, User $user)
    {
        // Check if user is approved (for non-super_admin users)
        if ($user->user_role !== 'super_admin' && !$user->is_approved) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'Your account is pending approval. Please contact an administrator.',
            ])->withInput($request->only('email', 'remember'));
        }
        
        return redirect()->intended('admin/dashboard');
    }

    /**
     * Handle vendor login
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleVendorLogin(Request $request, User $user)
    {
        $vendor = $user->vendor;
        
        if (!$vendor) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'Vendor profile not found. Please contact support.',
            ])->withInput($request->only('email', 'remember'));
        }
        
        if ($vendor->isPending()) {
            return redirect()->route('vendor.pending');
        }
        
        if ($vendor->isRejected()) {
            return redirect()->route('vendor.rejected');
        }
        
        if ($vendor->isSuspended()) {
            return redirect()->route('vendor.suspended');
        }
        
        // Vendor is approved, redirect to dashboard
        return redirect()->intended(route('vendor.dashboard'));
    }

    /**
     * Handle vendor staff login
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleVendorStaffLogin(Request $request, User $user)
    {
        $staffRecord = $user->vendorStaff;
        
        if (!$staffRecord) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'Staff profile not found. Please contact your vendor administrator.',
            ])->withInput($request->only('email', 'remember'));
        }
        
        // Check if staff is active
        if (!$staffRecord->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'Your staff account has been deactivated. Please contact your vendor administrator.',
            ])->withInput($request->only('email', 'remember'));
        }
        
        $vendor = $staffRecord->vendor;
        
        if (!$vendor) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'Associated vendor not found. Please contact support.',
            ])->withInput($request->only('email', 'remember'));
        }
        
        if ($vendor->isPending()) {
            return redirect()->route('vendor.pending');
        }
        
        if ($vendor->isRejected()) {
            return redirect()->route('vendor.rejected');
        }
        
        if ($vendor->isSuspended()) {
            return redirect()->route('vendor.suspended');
        }
        
        // Vendor is approved, redirect to dashboard
        return redirect()->intended(route('vendor.dashboard'));
    }

    /**
     * Handle frontend user login
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleFrontendUserLogin(Request $request, User $user)
    {
        // Get the frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // If admin approval is required, check if user is approved
        if ($accessPermission === 'admin_approval_required') {
            if (!$user->is_approved) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.',
                ])->withInput($request->only('email', 'remember'));
            }
        }
        
        // Migrate guest cart items to user's cart
        $this->migrateGuestCart($request);
        
        return redirect()->intended(route('frontend.home'))->with('login_success', true);
    }

    /**
     * Redirect user based on their role
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectBasedOnRole($user)
    {
        if (!($user instanceof User)) {
            return redirect()->route('frontend.home');
        }

        $role = $user->user_role;

        if (in_array($role, $this->adminRoles)) {
            return redirect()->route('dashboard');
        }

        if ($role === 'vendor' || $role === 'vendor_staff') {
            return redirect()->route('vendor.dashboard');
        }

        return redirect()->route('frontend.home');
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
