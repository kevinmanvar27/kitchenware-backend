<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminFeatureSetting extends Model
{
    protected $fillable = [
        'feature_key',
        'feature_name',
        'feature_description',
        'feature_group',
        'is_enabled',
        'allow_vendor_override',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'allow_vendor_override' => 'boolean',
    ];

    /**
     * Get all features grouped by category
     */
    public static function getGroupedFeatures()
    {
        return self::orderBy('sort_order')->get()->groupBy('feature_group');
    }

    /**
     * Check if a feature is globally enabled
     */
    public static function isFeatureEnabled(string $featureKey): bool
    {
        return Cache::remember("admin_feature_{$featureKey}", 3600, function () use ($featureKey) {
            $feature = self::where('feature_key', $featureKey)->first();
            return $feature ? $feature->is_enabled : true;
        });
    }

    /**
     * Check if vendor can override a disabled feature
     */
    public static function canVendorOverride(string $featureKey): bool
    {
        return Cache::remember("admin_feature_override_{$featureKey}", 3600, function () use ($featureKey) {
            $feature = self::where('feature_key', $featureKey)->first();
            return $feature ? $feature->allow_vendor_override : true;
        });
    }

    /**
     * Clear feature cache
     */
    public static function clearCache(?string $featureKey = null)
    {
        if ($featureKey) {
            Cache::forget("admin_feature_{$featureKey}");
            Cache::forget("admin_feature_override_{$featureKey}");
        } else {
            // Clear all feature caches
            $features = self::all();
            foreach ($features as $feature) {
                Cache::forget("admin_feature_{$feature->feature_key}");
                Cache::forget("admin_feature_override_{$feature->feature_key}");
            }
        }
    }

    /**
     * Get feature group display names
     */
    public static function getGroupDisplayNames(): array
    {
        return [
            'catalog' => 'Catalog Management',
            'sales' => 'Sales & Orders',
            'customers' => 'Customer Management',
            'team' => 'Team Management',
            'marketing' => 'Marketing & Analytics',
            'content' => 'Content',
            'settings' => 'Settings',
        ];
    }
}
