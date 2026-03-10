<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AdminFeatureSetting;
use App\Models\VendorFeatureSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeatureSettingsController extends Controller
{
    /**
     * Display vendor's feature settings
     * Shows only features that admin has disabled but allows vendor override
     */
    public function index()
    {
        $vendor = auth()->user()->vendor;
        
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Vendor account not found.');
        }

        // Get all features that vendor can override
        $adminFeatures = AdminFeatureSetting::where('is_enabled', false)
            ->where('allow_vendor_override', true)
            ->orderBy('sort_order')
            ->get();

        // Get vendor's current settings
        $vendorSettings = VendorFeatureSetting::where('vendor_id', $vendor->id)
            ->pluck('is_enabled', 'feature_key');

        // Build the features list with vendor's current preference
        $features = [];
        foreach ($adminFeatures as $feature) {
            $features[] = [
                'feature_key' => $feature->feature_key,
                'feature_name' => $feature->feature_name,
                'feature_description' => $feature->feature_description,
                'feature_group' => $feature->feature_group,
                'vendor_enabled' => $vendorSettings->get($feature->feature_key, false),
            ];
        }

        // Group features by category
        $groupedFeatures = collect($features)->groupBy('feature_group');
        $groupNames = AdminFeatureSetting::getGroupDisplayNames();

        return view('vendor.profile.feature-settings', compact('groupedFeatures', 'groupNames'));
    }

    /**
     * Update vendor's feature preferences
     */
    public function update(Request $request)
    {
        $vendor = auth()->user()->vendor;
        
        if (!$vendor) {
            return redirect()->route('vendor.dashboard')
                ->with('error', 'Vendor account not found.');
        }

        try {
            $features = $request->input('features', []);
            
            // Get all overrideable features
            $overrideableFeatures = AdminFeatureSetting::where('is_enabled', false)
                ->where('allow_vendor_override', true)
                ->pluck('feature_key')
                ->toArray();

            foreach ($overrideableFeatures as $featureKey) {
                // Use filter_var for proper boolean conversion
                // Handles "1", "0", true, false, null properly
                $isEnabled = filter_var($features[$featureKey] ?? false, FILTER_VALIDATE_BOOLEAN);
                
                VendorFeatureSetting::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'feature_key' => $featureKey,
                    ],
                    [
                        'is_enabled' => $isEnabled,
                    ]
                );

                // Clear cache for this vendor's feature
                VendorFeatureSetting::clearVendorCache($vendor->id, $featureKey);
            }

            return redirect()->back()->with('success', 'Feature settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating vendor feature settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update feature settings. Please try again.');
        }
    }

    /**
     * Toggle a single feature (AJAX)
     */
    public function toggle(Request $request, $featureKey)
    {
        $vendor = auth()->user()->vendor;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor account not found.',
            ], 404);
        }

        try {
            // Verify this feature can be overridden
            $adminFeature = AdminFeatureSetting::where('feature_key', $featureKey)
                ->where('is_enabled', false)
                ->where('allow_vendor_override', true)
                ->first();

            if (!$adminFeature) {
                return response()->json([
                    'success' => false,
                    'message' => 'This feature cannot be modified.',
                ], 403);
            }

            // Get or create vendor setting
            $vendorSetting = VendorFeatureSetting::firstOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'feature_key' => $featureKey,
                ],
                [
                    'is_enabled' => false,
                ]
            );

            // Toggle the setting
            $vendorSetting->is_enabled = !$vendorSetting->is_enabled;
            $vendorSetting->save();

            // Clear cache
            VendorFeatureSetting::clearVendorCache($vendor->id, $featureKey);

            return response()->json([
                'success' => true,
                'is_enabled' => $vendorSetting->is_enabled,
                'message' => 'Feature setting updated successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling vendor feature: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update feature setting.',
            ], 500);
        }
    }
}
