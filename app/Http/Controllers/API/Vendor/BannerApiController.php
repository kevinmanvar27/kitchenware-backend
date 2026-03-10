<?php

namespace App\Http\Controllers\API\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BannerApiController extends Controller
{
    /**
     * Get the authenticated vendor.
     * Returns vendor object or JSON error response.
     */
    private function getAuthenticatedVendor()
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check if user is a VendorCustomer (they shouldn't access vendor endpoints)
        if (get_class($user) === 'App\Models\VendorCustomer') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This endpoint is only for vendors and staff.'
            ], 403);
        }

        // Get vendor using the helper method for User model
        $vendor = $user->getActiveVendor();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        return $vendor;
    }

    /**
     * Get all banners for the authenticated vendor.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            // If vendor is a response (error), return it
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            // Get filter parameters
            $status = $request->query('status'); // 'active', 'inactive', or null for all
            $limit = $request->query('limit', 100); // Default 100, max 100

            $query = VendorBanner::where('vendor_id', $vendor->id);

            // Apply status filter
            if ($status === 'active') {
                $query->where('is_active', 1);
            } elseif ($status === 'inactive') {
                $query->where('is_active', 0);
            }

            // Get banners ordered by display_order
            $banners = $query->ordered()->limit($limit)->get();

            // Transform banners for API response
            $bannersData = $banners->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'vendor_id' => $banner->vendor_id,
                    'title' => $banner->title,
                    'image_url' => asset($banner->image_path),
                    'image_path' => $banner->image_path,
                    'redirect_url' => $banner->redirect_url,
                    'is_active' => (bool) $banner->is_active,
                    'display_order' => $banner->display_order,
                    'created_at' => $banner->created_at->toIso8601String(),
                    'updated_at' => $banner->updated_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Banners retrieved successfully',
                'data' => [
                    'banners' => $bannersData,
                    'total' => $bannersData->count(),
                    'active_count' => $banners->where('is_active', 1)->count(),
                    'inactive_count' => $banners->where('is_active', 0)->count(),
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
     * Get a single banner by ID.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            $banner = VendorBanner::where('vendor_id', $vendor->id)
                ->where('id', $id)
                ->first();

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Banner retrieved successfully',
                'data' => [
                    'id' => $banner->id,
                    'vendor_id' => $banner->vendor_id,
                    'title' => $banner->title,
                    'image_url' => asset($banner->image_path),
                    'image_path' => $banner->image_path,
                    'redirect_url' => $banner->redirect_url,
                    'is_active' => (bool) $banner->is_active,
                    'display_order' => $banner->display_order,
                    'created_at' => $banner->created_at->toIso8601String(),
                    'updated_at' => $banner->updated_at->toIso8601String(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new banner.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
                'redirect_url' => 'required|url|max:500',
                'is_active' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = 'banner_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $storedPath = $image->storeAs('vendor/' . $vendor->id . '/banners', $filename, 'public');
                $imagePath = 'storage/' . $storedPath;
            }

            // Create banner
            $banner = VendorBanner::create([
                'vendor_id' => $vendor->id,
                'title' => $request->title,
                'image_path' => $imagePath,
                'redirect_url' => $request->redirect_url,
                'is_active' => $request->input('is_active', true) ? 1 : 0,
                'display_order' => $request->input('display_order', 0),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner created successfully',
                'data' => [
                    'id' => $banner->id,
                    'vendor_id' => $banner->vendor_id,
                    'title' => $banner->title,
                    'image_url' => asset($banner->image_path),
                    'image_path' => $banner->image_path,
                    'redirect_url' => $banner->redirect_url,
                    'is_active' => (bool) $banner->is_active,
                    'display_order' => $banner->display_order,
                    'created_at' => $banner->created_at->toIso8601String(),
                    'updated_at' => $banner->updated_at->toIso8601String(),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing banner.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            $banner = VendorBanner::where('vendor_id', $vendor->id)
                ->where('id', $id)
                ->first();

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found'
                ], 404);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'redirect_url' => 'sometimes|required|url|max:500',
                'is_active' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle image upload if new image is provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($banner->image_path) {
                    $oldPath = str_replace('storage/', '', $banner->image_path);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                // Upload new image
                $image = $request->file('image');
                $filename = 'banner_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $storedPath = $image->storeAs('vendor/' . $vendor->id . '/banners', $filename, 'public');
                $banner->image_path = 'storage/' . $storedPath;
            }

            // Update banner fields
            if ($request->has('title')) {
                $banner->title = $request->title;
            }
            if ($request->has('redirect_url')) {
                $banner->redirect_url = $request->redirect_url;
            }
            if ($request->has('is_active')) {
                $banner->is_active = $request->is_active ? 1 : 0;
            }
            if ($request->has('display_order')) {
                $banner->display_order = $request->display_order;
            }

            $banner->save();

            return response()->json([
                'success' => true,
                'message' => 'Banner updated successfully',
                'data' => [
                    'id' => $banner->id,
                    'vendor_id' => $banner->vendor_id,
                    'title' => $banner->title,
                    'image_url' => asset($banner->image_path),
                    'image_path' => $banner->image_path,
                    'redirect_url' => $banner->redirect_url,
                    'is_active' => (bool) $banner->is_active,
                    'display_order' => $banner->display_order,
                    'created_at' => $banner->created_at->toIso8601String(),
                    'updated_at' => $banner->updated_at->toIso8601String(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a banner.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            $banner = VendorBanner::where('vendor_id', $vendor->id)
                ->where('id', $id)
                ->first();

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found'
                ], 404);
            }

            // Delete the banner (image will be auto-deleted via model event)
            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle banner active status.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            $banner = VendorBanner::where('vendor_id', $vendor->id)
                ->where('id', $id)
                ->first();

            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found'
                ], 404);
            }

            $banner->is_active = !$banner->is_active;
            $banner->save();

            return response()->json([
                'success' => true,
                'message' => 'Banner status updated successfully',
                'data' => [
                    'id' => $banner->id,
                    'is_active' => (bool) $banner->is_active
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle banner status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder banners.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'order' => 'required|array',
                'order.*.id' => 'required|integer|exists:vendor_banners,id',
                'order.*.display_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $order = $request->input('order', []);
            
            foreach ($order as $item) {
                VendorBanner::where('vendor_id', $vendor->id)
                    ->where('id', $item['id'])
                    ->update(['display_order' => $item['display_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Banners reordered successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder banners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banner statistics.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $vendor = $this->getAuthenticatedVendor();
            
            if ($vendor instanceof \Illuminate\Http\JsonResponse) {
                return $vendor;
            }

            $total = VendorBanner::where('vendor_id', $vendor->id)->count();
            $active = VendorBanner::where('vendor_id', $vendor->id)->where('is_active', 1)->count();
            $inactive = VendorBanner::where('vendor_id', $vendor->id)->where('is_active', 0)->count();
            $withLinks = VendorBanner::where('vendor_id', $vendor->id)->whereNotNull('redirect_url')->count();

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_banners' => $total,
                    'active_banners' => $active,
                    'inactive_banners' => $inactive,
                    'banners_with_links' => $withLinks,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
