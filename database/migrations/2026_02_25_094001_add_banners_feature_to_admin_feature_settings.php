<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the banners feature already exists
        $exists = DB::table('admin_feature_settings')
            ->where('feature_key', 'banners')
            ->exists();

        if (!$exists) {
            // Get the max sort_order to append at the end
            $maxSortOrder = DB::table('admin_feature_settings')->max('sort_order') ?? 0;

            // Add banners feature to admin feature settings
            DB::table('admin_feature_settings')->insert([
                'feature_key' => 'banners',
                'feature_name' => 'Banners',
                'feature_description' => 'Manage store promotional banners',
                'feature_group' => 'marketing',
                'is_enabled' => true,
                'allow_vendor_override' => true,
                'sort_order' => $maxSortOrder + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the banners feature
        DB::table('admin_feature_settings')
            ->where('feature_key', 'banners')
            ->delete();
    }
};
