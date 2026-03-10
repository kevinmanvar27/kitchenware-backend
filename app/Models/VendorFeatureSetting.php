<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class VendorFeatureSetting extends Model
{
    protected $fillable = [
        'vendor_id',
        'feature_key',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the vendor that owns this setting
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Check if a feature is enabled for a specific vendor
     * Logic:
     * 1. If admin has enabled the feature globally -> Feature is available
     * 2. If admin has disabled but allows override -> Check vendor's preference
     * 3. If admin has disabled and doesn't allow override -> Feature is disabled
     */
    public static function isFeatureEnabledForVendor(int $vendorId, string $featureKey): bool
    {
        return Cache::remember("vendor_{$vendorId}_feature_{$featureKey}", 3600, function () use ($vendorId, $featureKey) {
            // First check admin settings
            $adminFeature = AdminFeatureSetting::where('feature_key', $featureKey)->first();
            
            if (!$adminFeature) {
                return true; // Feature not defined, allow by default
            }

            // If admin has enabled the feature, it's available
            if ($adminFeature->is_enabled) {
                return true;
            }

            // Admin has disabled the feature
            // Check if vendor override is allowed
            if (!$adminFeature->allow_vendor_override) {
                return false; // No override allowed, feature is disabled
            }

            // Check vendor's preference
            $vendorSetting = self::where('vendor_id', $vendorId)
                ->where('feature_key', $featureKey)
                ->first();

            // If vendor has explicitly enabled it, allow
            return $vendorSetting ? $vendorSetting->is_enabled : false;
        });
    }

    /**
     * Get all feature settings for a vendor with admin context
     */
    public static function getVendorFeatureSettings(int $vendorId): array
    {
        $adminFeatures = AdminFeatureSetting::orderBy('sort_order')->get();
        $vendorSettings = self::where('vendor_id', $vendorId)->pluck('is_enabled', 'feature_key');
        
        $result = [];
        foreach ($adminFeatures as $feature) {
            $vendorEnabled = $vendorSettings->get($feature->feature_key, false);
            
            $result[] = [
                'feature_key' => $feature->feature_key,
                'feature_name' => $feature->feature_name,
                'feature_description' => $feature->feature_description,
                'feature_group' => $feature->feature_group,
                'admin_enabled' => $feature->is_enabled,
                'allow_vendor_override' => $feature->allow_vendor_override,
                'vendor_enabled' => $vendorEnabled,
                'effective_status' => self::isFeatureEnabledForVendor($vendorId, $feature->feature_key),
            ];
        }
        
        return $result;
    }

    /**
     * Clear vendor feature cache
     */
    public static function clearVendorCache(int $vendorId, ?string $featureKey = null)
    {
        if ($featureKey) {
            Cache::forget("vendor_{$vendorId}_feature_{$featureKey}");
        } else {
            $features = AdminFeatureSetting::all();
            foreach ($features as $feature) {
                Cache::forget("vendor_{$vendorId}_feature_{$feature->feature_key}");
            }
        }
    }

    /**
     * Clear all vendor caches for a specific feature
     */
    public static function clearAllVendorCachesForFeature(string $featureKey)
    {
        $vendors = Vendor::all();
        foreach ($vendors as $vendor) {
            Cache::forget("vendor_{$vendor->id}_feature_{$featureKey}");
        }
    }
}
