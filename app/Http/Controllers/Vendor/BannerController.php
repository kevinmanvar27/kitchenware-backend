<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorBanner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Display a listing of the vendor's banners.
     */
    public function index()
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        $banners = VendorBanner::where('vendor_id', $vendor->id)
            ->ordered()
            ->get();

        return view('vendor.banners.index', compact('banners', 'vendor'));
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create()
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        return view('vendor.banners.create', compact('vendor'));
    }

    /**
     * Store a newly created banner in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'redirect_url' => ['required', 'string', 'max:500', 'regex:/^(https?:\/\/|\/)/i'],
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ], [
            'redirect_url.regex' => 'The redirect URL must start with http://, https://, or / (for relative URLs).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = 'banner_' . time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('vendor/' . $vendor->id . '/banners', $filename, 'public');
            }

            // Create banner
            VendorBanner::create([
                'vendor_id' => $vendor->id,
                'title' => $request->title,
                'image_path' => 'storage/' . $imagePath,
                'redirect_url' => $request->redirect_url,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'display_order' => $request->display_order ?? 0,
            ]);

            return redirect()->route('vendor.banners.index')
                ->with('success', 'Banner created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create banner: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        $banner = VendorBanner::where('vendor_id', $vendor->id)
            ->findOrFail($id);

        return view('vendor.banners.edit', compact('banner', 'vendor'));
    }

    /**
     * Update the specified banner in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        $banner = VendorBanner::where('vendor_id', $vendor->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'redirect_url' => ['required', 'string', 'max:500', 'regex:/^(https?:\/\/|\/)/i'],
            'is_active' => 'nullable|boolean',
            'display_order' => 'nullable|integer|min:0',
        ], [
            'redirect_url.regex' => 'The redirect URL must start with http://, https://, or / (for relative URLs).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
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
                $imagePath = $image->storeAs('vendor/' . $vendor->id . '/banners', $filename, 'public');
                $banner->image_path = 'storage/' . $imagePath;
            }

            // Update banner
            $banner->title = $request->title;
            $banner->redirect_url = $request->redirect_url;
            $banner->is_active = $request->has('is_active') ? 1 : 0;
            $banner->display_order = $request->display_order ?? 0;
            $banner->save();

            return redirect()->route('vendor.banners.index')
                ->with('success', 'Banner updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update banner: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified banner from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        try {
            $banner = VendorBanner::where('vendor_id', $vendor->id)
                ->findOrFail($id);

            $banner->delete();

            return redirect()->route('vendor.banners.index')
                ->with('success', 'Banner deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete banner: ' . $e->getMessage());
        }
    }

    /**
     * Toggle banner active status.
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        try {
            $banner = VendorBanner::where('vendor_id', $vendor->id)
                ->findOrFail($id);

            $banner->is_active = !$banner->is_active;
            $banner->save();

            return redirect()->route('vendor.banners.index')
                ->with('success', 'Banner status updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update banner status: ' . $e->getMessage());
        }
    }

    /**
     * Reorder banners.
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();
        $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;

        try {
            $order = $request->input('order', []);
            
            foreach ($order as $item) {
                VendorBanner::where('vendor_id', $vendor->id)
                    ->where('id', $item['id'])
                    ->update(['display_order' => $item['display_order']]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get categories for the vendor (API endpoint for banner creation)
     */
    public function getCategories()
    {
        try {
            // Try to get authenticated user
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;
            
            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found for user'
                ], 404);
            }

            // Get all active categories (no parent_id column exists in this table)
            $categories = Category::where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'count' => $categories->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getCategories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products for the vendor (API endpoint for banner creation)
     */
    public function getProducts()
    {
        try {
            \Log::info('=== getProducts API called ===');
            
            $user = Auth::user();
            \Log::info('User:', ['id' => $user ? $user->id : null, 'email' => $user ? $user->email : null]);
            
            if (!$user) {
                \Log::error('No authenticated user found');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $vendor = $user->isVendor() ? $user->vendor : $user->vendorStaff->vendor;
            \Log::info('Vendor:', ['id' => $vendor ? $vendor->id : null, 'name' => $vendor ? $vendor->name : null]);
            
            if (!$vendor) {
                \Log::error('No vendor found for user');
                return response()->json([
                    'success' => false,
                    'message' => 'No vendor associated with this user'
                ], 403);
            }

            $products = Product::where('vendor_id', $vendor->id)
                ->whereIn('status', ['published', 'active'])
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            \Log::info('Products found:', ['count' => $products->count()]);

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getProducts: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error loading products: ' . $e->getMessage()
            ], 500);
        }
    }
}
