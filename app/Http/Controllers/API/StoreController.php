<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\VendorFollower;
use App\Models\VendorReview;

/**
 * @OA\Tag(
 *     name="Stores",
 *     description="API Endpoints for browsing vendor stores"
 * )
 */
class StoreController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/stores",
     *     summary="Get list of stores",
     *     description="Returns list of approved vendor stores",
     *     operationId="getStores",
     *     tags={"Stores"},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page (max 50)", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", description="Search by store name", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="featured", in="query", description="Filter featured stores only", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="city", in="query", description="Filter by city", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index(Request $request)
    {
        $query = Vendor::approved()
            ->with('user:id,name,email');

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store_name', 'like', '%' . $search . '%')
                  ->orWhere('store_description', 'like', '%' . $search . '%');
            });
        }

        // Filter featured stores
        if ($request->has('featured') && $request->featured) {
            $query->featured();
        }

        // Filter by city
        if ($request->has('city') && !empty($request->city)) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Order by priority and featured status
        $query->orderBy('is_featured', 'desc')
              ->orderBy('priority', 'desc')
              ->orderBy('store_name', 'asc');

        $perPage = min($request->get('per_page', 15), 50);
        $stores = $query->paginate($perPage);

        // Transform the data
        $stores->getCollection()->transform(function($vendor) {
            return [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_description' => $vendor->store_description,
                'store_logo_url' => $vendor->store_logo_url,
                'store_banner_url' => $vendor->store_banner_url,
                'banner_redirect_url' => $vendor->banner_redirect_url,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'is_featured' => $vendor->is_featured,
                'total_products' => $vendor->total_products,
                'owner_name' => $vendor->user->name ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Stores retrieved successfully',
            'data' => $stores
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stores/featured",
     *     summary="Get featured stores",
     *     description="Returns list of featured vendor stores",
     *     operationId="getFeaturedStores",
     *     tags={"Stores"},
     *     @OA\Parameter(name="limit", in="query", description="Number of stores to return (max 20)", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function featured(Request $request)
    {
        $limit = min($request->get('limit', 10), 20);

        $stores = Vendor::approved()
            ->featured()
            ->with('user:id,name')
            ->orderBy('priority', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($vendor) {
                return [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_description' => $vendor->store_description,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'banner_redirect_url' => $vendor->banner_redirect_url,
                    'city' => $vendor->city,
                    'total_products' => $vendor->total_products,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Featured stores retrieved successfully',
            'data' => $stores
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stores/{slug}",
     *     summary="Get store details",
     *     description="Returns detailed information about a specific store",
     *     operationId="getStoreBySlug",
     *     tags={"Stores"},
     *     @OA\Parameter(name="slug", in="path", description="Store slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function show($slug)
    {
        $vendor = Vendor::approved()
            ->where('store_slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store details retrieved successfully',
            'data' => [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_description' => $vendor->store_description,
                'store_logo_url' => $vendor->store_logo_url,
                'store_banner_url' => $vendor->store_banner_url,
                'banner_redirect_url' => $vendor->banner_redirect_url,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'country' => $vendor->country,
                'postal_code' => $vendor->postal_code,
                'is_featured' => $vendor->is_featured,
                'social_links' => $vendor->social_links,
                'total_products' => $vendor->total_products,
                'owner_name' => $vendor->user->name ?? null,
                'member_since' => $vendor->approved_at ? $vendor->approved_at->format('F Y') : $vendor->created_at->format('F Y'),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stores/{slug}/products",
     *     summary="Get store products",
     *     description="Returns list of products from a specific store",
     *     operationId="getStoreProducts",
     *     tags={"Stores"},
     *     @OA\Parameter(name="slug", in="path", description="Store slug", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="category_id", in="query", description="Filter by category", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort_by", in="query", description="Sort field (name, price, created_at)", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order (asc, desc)", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function products(Request $request, $slug)
    {
        $vendor = Vendor::approved()
            ->where('store_slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        $query = Product::where('vendor_id', $vendor->id)
            ->where('status', 'published')
            ->with(['variations']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->whereJsonContains('product_categories', [['category_id' => (int)$request->category_id]]);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'price') {
            $query->orderBy('selling_price', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Store products retrieved successfully',
            'data' => [
                'store' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                    'store_logo_url' => $vendor->store_logo_url,
                    'store_banner_url' => $vendor->store_banner_url,
                    'banner_redirect_url' => $vendor->banner_redirect_url,
                ],
                'products' => $products
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stores/{slug}/categories",
     *     summary="Get store categories",
     *     description="Returns list of categories available in a specific store",
     *     operationId="getStoreCategories",
     *     tags={"Stores"},
     *     @OA\Parameter(name="slug", in="path", description="Store slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function categories($slug)
    {
        $vendor = Vendor::approved()
            ->where('store_slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        $categories = $vendor->categories()
            ->where('is_active', true)
            ->with(['subcategories' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image_url' => $category->image ? $category->image->url : null,
                    'subcategories' => $category->subcategories->map(function($sub) {
                        return [
                            'id' => $sub->id,
                            'name' => $sub->name,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Store categories retrieved successfully',
            'data' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stores/{slug}/reviews",
     *     summary="Get store reviews",
     *     description="Returns list of reviews for a specific store",
     *     operationId="getStoreReviews",
     *     tags={"Stores"},
     *     @OA\Parameter(name="slug", in="path", description="Store slug", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function reviews(Request $request, $slug)
    {
        $vendor = Vendor::approved()
            ->where('store_slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $reviews = VendorReview::where('vendor_id', $vendor->id)
            ->approved()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Calculate rating statistics
        $ratingStats = VendorReview::where('vendor_id', $vendor->id)
            ->approved()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = array_sum($ratingStats);
        $averageRating = $totalReviews > 0 
            ? array_sum(array_map(function($rating, $count) { return $rating * $count; }, array_keys($ratingStats), $ratingStats)) / $totalReviews
            : 0;

        return response()->json([
            'success' => true,
            'message' => 'Store reviews retrieved successfully',
            'data' => [
                'store' => [
                    'id' => $vendor->id,
                    'store_name' => $vendor->store_name,
                    'store_slug' => $vendor->store_slug,
                ],
                'statistics' => [
                    'average_rating' => round($averageRating, 1),
                    'total_reviews' => $totalReviews,
                    'rating_breakdown' => [
                        5 => $ratingStats[5] ?? 0,
                        4 => $ratingStats[4] ?? 0,
                        3 => $ratingStats[3] ?? 0,
                        2 => $ratingStats[2] ?? 0,
                        1 => $ratingStats[1] ?? 0,
                    ]
                ],
                'reviews' => $reviews
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/stores/{slug}/follow",
     *     summary="Follow a store",
     *     description="Follow a vendor store",
     *     operationId="followStore",
     *     tags={"Stores"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="slug", in="path", description="Store slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Store followed successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function follow(Request $request, $slug)
    {
        $vendor = Vendor::approved()
            ->where('store_slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        $user = $request->user();

        // Check if already following
        $existingFollow = VendorFollower::where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingFollow) {
            return response()->json([
                'success' => false,
                'message' => 'You are already following this store'
            ], 400);
        }

        VendorFollower::create([
            'vendor_id' => $vendor->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Store followed successfully',
            'data' => [
                'is_following' => true,
                'followers_count' => $vendor->followers()->count()
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/stores/{slug}/unfollow",
     *     summary="Unfollow a store",
     *     description="Unfollow a vendor store",
     *     operationId="unfollowStore",
     *     tags={"Stores"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="slug", in="path", description="Store slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Store unfollowed successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function unfollow(Request $request, $slug)
    {
        $vendor = Vendor::approved()
            ->where('store_slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        $user = $request->user();

        $deleted = VendorFollower::where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'You are not following this store'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store unfollowed successfully',
            'data' => [
                'is_following' => false,
                'followers_count' => $vendor->followers()->count()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stores/{slug}/is-following",
     *     summary="Check if following a store",
     *     description="Check if the authenticated user is following a store",
     *     operationId="isFollowingStore",
     *     tags={"Stores"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="slug", in="path", description="Store slug", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
    public function isFollowing(Request $request, $slug)
    {
        $vendor = Vendor::approved()
            ->where('store_slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        $user = $request->user();
        $isFollowing = VendorFollower::where('vendor_id', $vendor->id)
            ->where('user_id', $user->id)
            ->exists();

        return response()->json([
            'success' => true,
            'message' => 'Follow status retrieved successfully',
            'data' => [
                'is_following' => $isFollowing,
                'followers_count' => $vendor->followers()->count()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-followed-stores",
     *     summary="Get followed stores",
     *     description="Get list of stores the authenticated user is following",
     *     operationId="getMyFollowedStores",
     *     tags={"Stores"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function myFollowedStores(Request $request)
    {
        $user = $request->user();
        $perPage = min($request->get('per_page', 15), 50);

        $followedVendorIds = VendorFollower::where('user_id', $user->id)
            ->pluck('vendor_id');

        $stores = Vendor::approved()
            ->whereIn('id', $followedVendorIds)
            ->with('user:id,name')
            ->paginate($perPage);

        $stores->getCollection()->transform(function($vendor) {
            return [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_description' => $vendor->store_description,
                'store_logo_url' => $vendor->store_logo_url,
                'store_banner_url' => $vendor->store_banner_url,
                'banner_redirect_url' => $vendor->banner_redirect_url,
                'city' => $vendor->city,
                'total_products' => $vendor->total_products,
                'is_following' => true,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Followed stores retrieved successfully',
            'data' => $stores
        ]);
    }
}
