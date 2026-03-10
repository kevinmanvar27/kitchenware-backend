<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display the wishlist page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $wishlistItems = [];
        $totalItems = 0;
        
        if ($user) {
            // Get wishlist items for authenticated user
            // Note: mainPhoto is an accessor on Product, not a relationship, so we don't eager load it
            $wishlistQuery = Wishlist::with(['product.variations', 'vendor'])
                ->where('user_id', $user->id)
                ->whereHas('product', function ($query) {
                    $query->whereIn('status', ['published', 'active']);
                });
            
            $wishlistItems = $wishlistQuery->get();
            $totalItems = $wishlistItems->count();
            
            // Group by vendor if needed
            $wishlistByVendor = $wishlistItems->groupBy('vendor_id');
        } else {
            // For guest users, they will see empty wishlist with login prompt
            $wishlistByVendor = collect();
        }
        
        return view('frontend.wishlist', [
            'wishlistItems' => $wishlistItems,
            'wishlistByVendor' => $wishlistByVendor ?? collect(),
            'totalItems' => $totalItems,
        ]);
    }
    
    /**
     * Add product to wishlist (web route)
     */
    public function add(Request $request, $productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add items to wishlist.',
                'redirect' => route('login')
            ], 401);
        }
        
        $user = Auth::user();
        
        // Check if product exists and is active
        $product = Product::where('id', $productId)
            ->whereIn('status', ['published', 'active'])
            ->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or is no longer available.'
            ], 404);
        }
        
        // Check if already in wishlist
        $existingItem = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        
        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already in your wishlist.'
            ], 400);
        }
        
        // Add to wishlist
        $wishlistItem = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'vendor_id' => $product->vendor_id,
        ]);
        
        $totalItems = Wishlist::where('user_id', $user->id)->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist.',
            'wishlist_count' => $totalItems
        ]);
    }
    
    /**
     * Remove product from wishlist (web route)
     */
    public function remove(Request $request, $productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to manage your wishlist.'
            ], 401);
        }
        
        $user = Auth::user();
        
        $wishlistItem = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        
        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in your wishlist.'
            ], 404);
        }
        
        $wishlistItem->delete();
        
        $totalItems = Wishlist::where('user_id', $user->id)->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist.',
            'wishlist_count' => $totalItems
        ]);
    }
    
    /**
     * Clear entire wishlist
     */
    public function clear(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to manage your wishlist.'
            ], 401);
        }
        
        $user = Auth::user();
        $itemsRemoved = Wishlist::where('user_id', $user->id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully.',
            'items_removed' => $itemsRemoved
        ]);
    }
    
    /**
     * Get wishlist count
     */
    public function count(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'wishlist_count' => 0
            ]);
        }
        
        $user = Auth::user();
        $count = Wishlist::where('user_id', $user->id)->count();
        
        return response()->json([
            'success' => true,
            'wishlist_count' => $count
        ]);
    }
    
    /**
     * Check if product is in wishlist
     */
    public function check(Request $request, $productId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'is_in_wishlist' => false
            ]);
        }
        
        $user = Auth::user();
        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();
        
        return response()->json([
            'success' => true,
            'is_in_wishlist' => $exists
        ]);
    }
    
    /**
     * Check multiple products in wishlist
     */
    public function checkMultiple(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => [],
                    'total_in_wishlist' => 0
                ]
            ]);
        }
        
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);
        
        $user = Auth::user();
        $productIds = $request->product_ids;
        
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->whereIn('product_id', $productIds)
            ->get();
        
        // Create a map of product_id => wishlist status
        $statusMap = [];
        foreach ($productIds as $productId) {
            $item = $wishlistItems->firstWhere('product_id', $productId);
            $statusMap[$productId] = [
                'is_in_wishlist' => $item !== null,
                'added_at' => $item ? $item->created_at : null,
                'vendor_id' => $item ? $item->vendor_id : null,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'products' => $statusMap,
                'total_in_wishlist' => $wishlistItems->count(),
            ]
        ]);
    }
}
