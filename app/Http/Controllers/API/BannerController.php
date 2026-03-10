<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VendorBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BannerController extends Controller
{
    /**
     * Get banners for the authenticated customer's vendor store.
     * This endpoint works for logged-in vendor customers.
     * Requires authentication with vendor customer token.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function myVendorBanners(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login first.'
                ], 401);
            }

            // Check if user is a VendorCustomer
            if (get_class($user) !== 'App\Models\VendorCustomer') {
                return response()->json([
                    'success' => false,
                    'message' => 'This endpoint is only for vendor customers.'
                ], 403);
            }

            // Get the customer's vendor
            $vendor = $user->vendor;

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found'
                ], 404);
            }

            // Get active banners for this vendor
            $banners = VendorBanner::where('vendor_id', $vendor->id)
                ->where('is_active', 1)
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Banners retrieved successfully',
                'data' => [
                    'banners' => $this->transformBanners($banners),
                    'total' => $banners->count(),
                    'vendor' => [
                        'id' => $vendor->id,
                        'store_name' => $vendor->store_name,
                        'store_slug' => $vendor->store_slug,
                        'store_logo' => $vendor->store_logo ? asset($vendor->store_logo) : null,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve banners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform banners collection to API response format.
     * 
     * @param \Illuminate\Support\Collection $banners
     * @return \Illuminate\Support\Collection
     */
    private function transformBanners($banners)
    {
        return $banners->map(function ($banner) {
            return [
                'id' => $banner->id,
                'title' => $banner->title,
                'image_url' => asset($banner->image_path),
                'redirect_url' => $banner->redirect_url,
                'display_order' => $banner->display_order,
            ];
        });
    }
}
