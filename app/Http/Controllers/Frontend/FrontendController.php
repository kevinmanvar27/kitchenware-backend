<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\Vendor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Models\ProductView;
use App\Services\GeoLocationService;

class FrontendController extends Controller
{
    /**
     * Show the home page - displays ALL products (admin + all vendors)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch ALL published products (admin + all vendors)
        $products = Product::where('status', 'published')
            ->with(['variations', 'vendor'])
            ->latest()
            ->get();
        
        // Get categories that have products
        $categoryIds = collect();
        foreach ($products as $product) {
            if ($product->product_categories) {
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id'])) {
                        $categoryIds->push($catData['category_id']);
                    }
                }
            }
        }
        $categoryIds = $categoryIds->unique()->values();
        
        // Fetch those categories
        $categories = Category::whereIn('id', $categoryIds)
            ->where('is_active', true)
            ->with('subCategories')
            ->get()
            ->map(function ($category) use ($products) {
                // Count products for this category
                $productCount = $products->filter(function ($product) use ($category) {
                    if (!$product->product_categories) return false;
                    foreach ($product->product_categories as $catData) {
                        if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                            return true;
                        }
                    }
                    return false;
                })->count();
                
                $category->product_count = $productCount;
                return $category;
            })
            ->filter(function ($category) {
                return $category->product_count > 0;
            })
            ->values();

        return view('frontend.home', compact('categories', 'products'));
    }
    
    /**
     * Show vendor store page - displays all products from a specific vendor
     *
     * @param string $vendorSlug
     * @return \Illuminate\View\View
     */
    public function vendorStore($vendorSlug)
    {
        $vendor = Vendor::where('store_slug', $vendorSlug)
            ->where('status', Vendor::STATUS_APPROVED)
            ->first();
        
        if (!$vendor) {
            abort(404, 'Store not found');
        }
        
        // Fetch products belonging to this vendor
        $products = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->with(['variations'])
            ->latest()
            ->get();
        
        // Get featured products for this vendor
        $featuredProducts = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->where('is_featured', true)
            ->where('in_stock', true)
            ->with(['variations'])
            ->latest()
            ->limit(8)
            ->get();
        
        // Get categories that have products from this vendor
        $categoryIds = collect();
        foreach ($products as $product) {
            if ($product->product_categories) {
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id'])) {
                        $categoryIds->push($catData['category_id']);
                    }
                }
            }
        }
        $categoryIds = $categoryIds->unique()->values();
        
        // Fetch those categories
        $categories = Category::whereIn('id', $categoryIds)
            ->where('is_active', true)
            ->with('subCategories')
            ->get()
            ->map(function ($category) use ($products) {
                // Count products for this category
                $productCount = $products->filter(function ($product) use ($category) {
                    if (!$product->product_categories) return false;
                    foreach ($product->product_categories as $catData) {
                        if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                            return true;
                        }
                    }
                    return false;
                })->count();
                
                $category->product_count = $productCount;
                return $category;
            })
            ->filter(function ($category) {
                return $category->product_count > 0;
            })
            ->values();

        // Get vendor store settings
        $settings = $vendor->store_settings ?? [];
        
        return view('frontend.vendor-store', compact('vendor', 'products', 'featuredProducts', 'categories', 'settings'));
    }
    
    /**
     * Show the user profile page
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        return view('frontend.profile', ['user' => Auth::user()]);
    }
    
    /**
     * Show the category detail page - shows ALL products in category (admin + vendors)
     *
     * @param  \App\Models\Category  $category
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showCategory(Category $category, Request $request)
    {
        // Check if category is active
        if (!$category->is_active) {
            abort(404);
        }
        
        // Load active subcategories with their images
        // Only show subcategories that have products
        $subCategories = $category->subCategories()
            ->where('is_active', true)
            ->get()
            ->filter(function ($subCategory) use ($category) {
                // Build product query - ALL products (admin + vendors)
                $productsQuery = Product::where('status', 'published');
                
                // Check if this subcategory has any products in this category
                $products = $productsQuery->get()
                    ->filter(function ($product) use ($category, $subCategory) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        // Check if product belongs to both the main category and this specific subcategory
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $category->id &&
                                isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                return true;
                            }
                        }
                        
                        return false;
                    });
                
                return $products->count() > 0;
            })
            ->values();
            
        // Get the selected subcategory ID from the request
        $selectedSubcategoryId = $request->query('subcategory');
        
        // Get the sort parameter from the request
        $sort = $request->query('sort', 'default');
        
        // Load ALL products associated with this category (admin + vendors)
        $productsQuery = Product::where('status', 'published')
            ->with(['variations', 'vendor']);
        
        $products = $productsQuery->get()
            ->filter(function ($product) use ($category, $selectedSubcategoryId) {
                if (!$product->product_categories) {
                    return false;
                }
                
                // Check if product belongs to the main category
                $belongsToCategory = false;
                foreach ($product->product_categories as $catData) {
                    if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
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
            
        // Sort products based on the sort parameter
        if ($sort === 'name') {
            $products = $products->sortBy('name');
        } elseif ($sort === 'price-low') {
            $products = $products->sortBy('selling_price');
        } elseif ($sort === 'price-high') {
            $products = $products->sortByDesc('selling_price');
        }
            
        // SEO meta tags
        $metaTitle = $category->name . ' - ' . setting('site_title', 'Frontend App');
        $metaDescription = $category->description ?? 'Explore products in ' . $category->name . ' category';
        
        // If request is AJAX, return only the products partial view
        if ($request->ajax()) {
            return view('frontend.partials.products-list', compact('products'));
        }
        
        return view('frontend.category', compact('category', 'subCategories', 'products', 'metaTitle', 'metaDescription', 'selectedSubcategoryId', 'sort'));
    }
    
    /**
     * Show the product detail page
     *
     * @param  string|null  $vendorSlugOrProduct  Vendor slug when called from vendor store route, or Product when called from direct product route
     * @param  \App\Models\Product|null  $product  Product instance when called from vendor store route
     * @return \Illuminate\View\View
     */
    public function showProduct($vendorSlugOrProduct = null, Product $product = null)
    {
        // Handle both route scenarios:
        // 1. Direct product route: /product/{product:slug} - $vendorSlugOrProduct is Product instance
        // 2. Vendor product route: /store/{vendorSlug}/product/{product:slug} - $vendorSlugOrProduct is string, $product is Product instance
        
        if ($vendorSlugOrProduct instanceof Product) {
            // Scenario 1: Direct product route
            $product = $vendorSlugOrProduct;
        } elseif ($product === null) {
            // If we don't have a product at all, something went wrong
            abort(404);
        }
        // Scenario 2: $product is already set correctly from the second parameter
        
        // Check if product is published
        if ($product->status !== 'published') {
            abort(404);
        }
        
        // Load vendor if product has one
        $vendor = null;
        if ($product->vendor_id) {
            $vendor = $product->vendor;
        }
        
        // Load variations for the product
        $product->load('variations');
        
        // Track product view
        $this->trackProductView($product);
        
        // SEO meta tags
        $metaTitle = $product->name . ' - ' . setting('site_title', 'Frontend App');
        $metaDescription = $product->meta_description ?? Str::limit($product->description, 160);
        
        return view('frontend.product', compact('product', 'metaTitle', 'metaDescription', 'vendor'));
    }
    
    /**
     * Track product view for analytics
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    protected function trackProductView(Product $product)
    {
        try {
            $request = request();
            $sessionId = session()->getId();
            $userAgent = $request->userAgent();
            $ipAddress = $request->ip();
            
            // Prevent duplicate views from same session within 30 minutes
            $recentView = ProductView::where('product_id', $product->id)
                ->where('session_id', $sessionId)
                ->where('created_at', '>=', now()->subMinutes(30))
                ->exists();
            
            if (!$recentView) {
                // Get location data from IP
                $location = GeoLocationService::getLocation($ipAddress);
                
                ProductView::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'session_id' => $sessionId,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'referrer' => $request->header('referer'),
                    'device_type' => ProductView::detectDeviceType($userAgent),
                    'browser' => ProductView::detectBrowser($userAgent),
                    'country' => $location['country'],
                    'country_code' => $location['country_code'],
                    'region' => $location['region'],
                    'city' => $location['city'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break product page if tracking fails
            Log::error('Product view tracking failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get subcategories for a category via AJAX
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function getSubcategories(Category $category)
    {
        // Load active subcategories with their images
        $subCategories = $category->subCategories()
            ->where('is_active', true)
            ->get();
            
        return response()->view('frontend.partials.subcategories', compact('subCategories'));
    }
    
    /**
     * Update the user profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate the request
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        
        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile_number = $request->mobile_number;
        $user->date_of_birth = $request->date_of_birth;
        $user->address = $request->address;
        $user->save();
        
        return redirect()->route('frontend.profile')->with('success', 'Profile updated successfully.');
    }
    
    /**
     * Update the user's avatar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAvatar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate only the avatar field
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
        ], [
            'avatar.required' => 'Please select an image to upload.',
            'avatar.image' => 'The file must be an image.',
            'avatar.max' => 'The image may not be greater than 2MB.',
        ]);
        
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }
        
        // Store new avatar
        $avatarName = time() . '_' . $user->id . '.' . $request->file('avatar')->extension();
        $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
        $user->avatar = $avatarName;
        $user->save();
        
        return redirect()->route('frontend.profile')->with('success', 'Profile picture updated successfully.');
    }
    
    /**
     * Remove the user's avatar
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeAvatar()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Delete avatar file if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->avatar = null;
            $user->save();
        }
        
        return redirect()->route('frontend.profile')->with('success', 'Profile picture removed successfully.');
    }
    
    /**
     * Change the user's password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Validate the request
        $request->validate([
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'The new password must be different from your current password.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);
        
        // Update password
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('frontend.profile')->with('success', 'Password changed successfully.');
    }
    
    /**
     * Show the category detail page for a vendor store
     * Wrapper method to handle vendor route parameter order
     *
     * @param  string  $vendorSlug
     * @param  \App\Models\Category  $category
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showVendorCategory($vendorSlug, Category $category, Request $request)
    {
        return $this->showCategory($category, $request);
    }
    
    /**
     * Show the product detail page for a vendor store
     * Wrapper method to handle vendor route parameter order
     *
     * @param  string  $vendorSlug
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function showVendorProduct($vendorSlug, Product $product)
    {
        return $this->showProduct($product);
    }
}