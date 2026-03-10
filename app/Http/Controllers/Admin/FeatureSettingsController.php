<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminFeatureSetting;
use App\Models\VendorFeatureSetting;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeatureSettingsController extends Controller
{
    use LogsActivity;

    /**
     * Display feature settings page
     */
    public function index()
    {
        $groupedFeatures = AdminFeatureSetting::getGroupedFeatures();
        $groupNames = AdminFeatureSetting::getGroupDisplayNames();
        
        return view('admin.settings.features', compact('groupedFeatures', 'groupNames'));
    }

    /**
     * Update feature settings
     */
    public function update(Request $request)
    {
        try {
            $features = $request->input('features', []);
            
            // Get all existing features to handle any that might not be in the request
            $allFeatures = AdminFeatureSetting::all()->keyBy('feature_key');
            
            foreach ($features as $featureKey => $settings) {
                $feature = $allFeatures->get($featureKey);
                
                if ($feature) {
                    $oldEnabled = $feature->is_enabled;
                    $oldOverride = $feature->allow_vendor_override;
                    
                    // Convert values to boolean properly
                    // The value can be "1", "0", 1, 0, true, false, or not set
                    $isEnabled = filter_var($settings['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $allowOverride = filter_var($settings['allow_vendor_override'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    
                    $feature->update([
                        'is_enabled' => $isEnabled,
                        'allow_vendor_override' => $allowOverride,
                    ]);
                    
                    // Clear caches
                    AdminFeatureSetting::clearCache($featureKey);
                    VendorFeatureSetting::clearAllVendorCachesForFeature($featureKey);
                    
                    // Log if changed
                    if ($oldEnabled !== $feature->is_enabled || $oldOverride !== $feature->allow_vendor_override) {
                        $this->logAdminActivity('updated', "Updated feature '{$feature->feature_name}' settings", $feature);
                    }
                }
            }
            
            return redirect()->back()->with('success', 'Feature settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating feature settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update feature settings. Please try again.');
        }
    }

    /**
     * Toggle a single feature (AJAX)
     */
    public function toggle(Request $request, $featureKey)
    {
        try {
            $feature = AdminFeatureSetting::where('feature_key', $featureKey)->firstOrFail();
            
            $field = $request->input('field', 'is_enabled');
            
            if ($field === 'is_enabled') {
                $feature->is_enabled = !$feature->is_enabled;
            } elseif ($field === 'allow_vendor_override') {
                $feature->allow_vendor_override = !$feature->allow_vendor_override;
            }
            
            $feature->save();
            
            // Clear caches
            AdminFeatureSetting::clearCache($featureKey);
            VendorFeatureSetting::clearAllVendorCachesForFeature($featureKey);
            
            $this->logAdminActivity('toggled', "Toggled {$field} for feature '{$feature->feature_name}'", $feature);
            
            return response()->json([
                'success' => true,
                'is_enabled' => $feature->is_enabled,
                'allow_vendor_override' => $feature->allow_vendor_override,
                'message' => 'Feature setting updated successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling feature: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update feature setting.',
            ], 500);
        }
    }
}
