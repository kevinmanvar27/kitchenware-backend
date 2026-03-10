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
        // Add vendor-specific permissions
        $vendorPermissions = [
            // Vendor Management (Super Admin Only)
            ['name' => 'viewAny_vendor', 'display_name' => 'View Vendors', 'description' => 'Can view all vendors'],
            ['name' => 'create_vendor', 'display_name' => 'Create Vendor', 'description' => 'Can create vendors'],
            ['name' => 'update_vendor', 'display_name' => 'Update Vendor', 'description' => 'Can update vendors'],
            ['name' => 'delete_vendor', 'display_name' => 'Delete Vendor', 'description' => 'Can delete vendors'],
            ['name' => 'approve_vendor', 'display_name' => 'Approve Vendor', 'description' => 'Can approve/reject vendors'],
            ['name' => 'manage_vendor_commission', 'display_name' => 'Manage Vendor Commission', 'description' => 'Can manage vendor commission rates'],
            
            // Vendor Staff Management
            ['name' => 'viewAny_vendor_staff', 'display_name' => 'View Vendor Staff', 'description' => 'Can view vendor staff'],
            ['name' => 'create_vendor_staff', 'display_name' => 'Create Vendor Staff', 'description' => 'Can create vendor staff'],
            ['name' => 'update_vendor_staff', 'display_name' => 'Update Vendor Staff', 'description' => 'Can update vendor staff'],
            ['name' => 'delete_vendor_staff', 'display_name' => 'Delete Vendor Staff', 'description' => 'Can delete vendor staff'],
        ];

        $now = now();
        foreach ($vendorPermissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'description' => $permission['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Create vendor role
        $vendorRole = DB::table('roles')->where('name', 'vendor')->first();
        if (!$vendorRole) {
            DB::table('roles')->insert([
                'name' => 'vendor',
                'display_name' => 'Vendor',
                'description' => 'Vendor role with store management capabilities',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Create vendor_staff role
        $vendorStaffRole = DB::table('roles')->where('name', 'vendor_staff')->first();
        if (!$vendorStaffRole) {
            DB::table('roles')->insert([
                'name' => 'vendor_staff',
                'display_name' => 'Vendor Staff',
                'description' => 'Staff member working for a vendor',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove vendor permissions
        DB::table('permissions')->whereIn('name', [
            'viewAny_vendor',
            'create_vendor',
            'update_vendor',
            'delete_vendor',
            'approve_vendor',
            'manage_vendor_commission',
            'viewAny_vendor_staff',
            'create_vendor_staff',
            'update_vendor_staff',
            'delete_vendor_staff',
        ])->delete();

        // Remove vendor roles
        DB::table('roles')->whereIn('name', ['vendor', 'vendor_staff'])->delete();
    }
};
