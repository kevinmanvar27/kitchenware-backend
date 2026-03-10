<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\ShoppingCartItem;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Home",
 *     description="API Endpoints for Home/Dashboard Screen"
 * )
 */
class HomeController extends ApiController
{
    /**
     * Get home screen data
     * 
     * @OA\Get(
     *      path="/api/v1/home",
     *      operationId="getHomeData",
     *      tags={"Home"},
     *      summary="Get home screen data",
     *      description="Returns all data needed for the home screen including categories, featured products, and user-specific data. Authentication is optional - if authenticated, returns user-specific data like cart count and wishlist count.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="categories", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="featured_products", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="latest_products", type="array", @OA\Items(type="object")),
     *                  @OA\Property(property="cart_count", type="integer", example=5),
     *                  @OA\Property(property="unread_notifications_count", type="integer", example=3),
     *                  @OA\Property(property="announcements", type="array", @OA\Items(type="object")),
     *              ),
     *              @OA\Property(property="message", type="string", example="Home data retrieved successfully.")
     *          )
     *       ),
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get active categories with images and subcategories
        $categories = $this->getCategories();
        
        // Get featured/latest products
        $featuredProducts = $this->getFeaturedProducts($user, 10);
        $latestProducts = $this->getLatestProducts($user, 10);
        
        // Get user-specific data
        $cartCount = 0;
        $unreadNotificationsCount = 0;
        $wishlistCount = 0;
        
        if ($user) {
            $cartCount = ShoppingCartItem::where('user_id', $user->id)->sum('quantity');
            $unreadNotificationsCount = Notification::where('user_id', $user->id)
                ->where('read', false)
                ->count();
            $wishlistCount = $user->wishlistItems()->count();
        }
        
        // Get announcements/banners from settings
        $announcements = $this->getAnnouncements();
        
        // Get app branding
        $branding = $this->getBranding();
        
        $data = [
            'categories' => $categories,
            'featured_products' => $featuredProducts,
            'latest_products' => $latestProducts,
            'cart_count' => $cartCount,
            'unread_notifications_count' => $unreadNotificationsCount,
            'wishlist_count' => $wishlistCount,
            'announcements' => $announcements,
            'branding' => $branding,
        ];
        
        return $this->sendResponse($data, 'Home data retrieved successfully.');
    }
    
    /**
     * Get active categories with product counts
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getCategories()
    {
        $categories = Category::where('is_active', true)
            ->with(['image', 'subCategories' => function ($query) {
                $query->where('is_active', true)->with('image');
            }])
            ->get()
            ->map(function ($category) {
                // Count products in this category
                $productCount = Product::where('status', 'published')
                    ->get()
                    ->filter(function ($product) use ($category) {
                        if (!$product->product_categories) {
                            return false;
                        }
                        
                        foreach ($product->product_categories as $catData) {
                            if (isset($catData['category_id']) && $catData['category_id'] == $category->id) {
                                return true;
                            }
                        }
                        
                        return false;
                    })
                    ->count();
                
                $category->product_count = $productCount;
                
                // Add product count to subcategories
                $category->subCategories->transform(function ($subCategory) use ($category) {
                    $subProductCount = Product::where('status', 'published')
                        ->get()
                        ->filter(function ($product) use ($category, $subCategory) {
                            if (!$product->product_categories) {
                                return false;
                            }
                            
                            foreach ($product->product_categories as $catData) {
                                if (isset($catData['category_id']) && $catData['category_id'] == $category->id &&
                                    isset($catData['subcategory_ids']) && in_array($subCategory->id, $catData['subcategory_ids'])) {
                                    return true;
                                }
                            }
                            
                            return false;
                        })
                        ->count();
                    
                    $subCategory->product_count = $subProductCount;
                    return $subCategory;
                });
                
                return $category;
            })
            ->filter(function ($category) {
                // Only include categories that have products
                return $category->product_count > 0;
            })
            ->values();
        
        return $categories;
    }
    
    /**
     * Get featured products
     * 
     * @param \App\Models\User|null $user
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getFeaturedProducts($user, $limit = 10)
    {
        // Get featured products that are published and in stock
        $products = Product::where('status', 'published')
            ->where('in_stock', true)
            ->where('is_featured', true)
            ->with(['variations'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
        
        // If no featured products, fallback to recently updated products
        if ($products->isEmpty()) {
            $products = Product::where('status', 'published')
                ->where('in_stock', true)
                ->with(['variations'])
                ->orderBy('updated_at', 'desc')
                ->limit($limit)
                ->get();
        }
        
        return $this->transformProducts($products, $user);
    }
    
    /**
     * Get latest products
     * 
     * @param \App\Models\User|null $user
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    private function getLatestProducts($user, $limit = 10)
    {
        $products = Product::where('status', 'published')
            ->with(['variations'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        return $this->transformProducts($products, $user);
    }
    
    /**
     * Transform products collection with proper formatting
     * 
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @param \App\Models\User|null $user
     * @return \Illuminate\Support\Collection
     */
    private function transformProducts($products, $user)
    {
        return $products->map(function ($product) use ($user) {
            $priceToUse = (!is_null($product->selling_price) && $product->selling_price !== '' && $product->selling_price >= 0) 
                ? $product->selling_price 
                : $product->mrp;
            
            $discountedPrice = function_exists('calculateDiscountedPrice') 
                ? calculateDiscountedPrice($priceToUse, $user) 
                : $priceToUse;
            
            $priceRange = $product->price_range ?? ['min' => $priceToUse, 'max' => $priceToUse];
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'product_type' => $product->product_type ?? 'simple',
                'mrp' => $product->mrp,
                'selling_price' => $product->selling_price,
                'discounted_price' => $discountedPrice,
                'price_range' => $priceRange,
                'in_stock' => $product->in_stock,
                'stock_quantity' => $product->stock_quantity,
                'main_photo_url' => $product->mainPhoto?->url,
                'has_variations' => $product->isVariable(),
                'variations_count' => $product->variations->count(),
            ];
        });
    }
    
    /**
     * Add discounted prices to products collection (Legacy - kept for backward compatibility)
     * 
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @param \App\Models\User|null $user
     * @return \Illuminate\Support\Collection
     */
    private function addDiscountedPrices($products, $user)
    {
        return $products->map(function ($product) use ($user) {
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
    }
    
    /**
     * Get announcements/banners
     * 
     * @return array
     */
    private function getAnnouncements()
    {
        $setting = Setting::first();
        
        $announcements = [];
        
        // Get announcement text if set
        $announcementText = $setting->announcement_text ?? null;
        $announcementEnabled = $setting->announcement_enabled ?? false;
        
        if ($announcementEnabled && $announcementText) {
            $announcements[] = [
                'type' => 'text',
                'content' => $announcementText,
                'background_color' => $setting->announcement_bg_color ?? '#007bff',
                'text_color' => $setting->announcement_text_color ?? '#ffffff',
            ];
        }
        
        // Get banner images if set (stored as JSON array in settings)
        $bannerImages = $setting->banner_images ?? null;
        if ($bannerImages && is_array($bannerImages)) {
            foreach ($bannerImages as $banner) {
                $announcements[] = [
                    'type' => 'banner',
                    'image_url' => $banner['url'] ?? null,
                    'link_url' => $banner['link'] ?? null,
                    'title' => $banner['title'] ?? null,
                ];
            }
        }
        
        return $announcements;
    }
    
    /**
     * Get app branding information
     * 
     * @return array
     */
    private function getBranding()
    {
        $setting = Setting::first();
        
        return [
            'brand_name' => $setting->brand_name ?? config('app.name'),
            'tagline' => $setting->tagline ?? '',
            'logo_url' => $setting->logo_url ?? null,
            'primary_color' => $setting->primary_color ?? '#007bff',
            'secondary_color' => $setting->secondary_color ?? '#6c757d',
        ];
    }
}
