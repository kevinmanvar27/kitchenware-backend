<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Vendor Management Permissions
        $vendorPermissions = [
            [
                'name' => 'viewAny_vendor',
                'display_name' => 'View Any Vendor',
                'description' => 'View all vendors'
            ],
            [
                'name' => 'view_vendor',
                'display_name' => 'View Vendor',
                'description' => 'View a specific vendor'
            ],
            [
                'name' => 'create_vendor',
                'display_name' => 'Create Vendor',
                'description' => 'Create new vendors'
            ],
            [
                'name' => 'update_vendor',
                'display_name' => 'Update Vendor',
                'description' => 'Modify existing vendors'
            ],
            [
                'name' => 'delete_vendor',
                'display_name' => 'Delete Vendor',
                'description' => 'Remove vendors'
            ],
            [
                'name' => 'approve_vendor',
                'display_name' => 'Approve Vendor',
                'description' => 'Approve vendor registrations'
            ],
        ];

        // Add Referral Earnings Management Permissions
        $referralEarningsPermissions = [
            [
                'name' => 'viewAny_referral_earning',
                'display_name' => 'View Referral Earnings',
                'description' => 'View all referral earnings'
            ],
            [
                'name' => 'view_referral_earning',
                'display_name' => 'View Referral Earning Details',
                'description' => 'View specific referral earning details'
            ],
            [
                'name' => 'approve_referral_earning',
                'display_name' => 'Approve Referral Earnings',
                'description' => 'Approve referral earnings for payout'
            ],
            [
                'name' => 'create_referral_payout',
                'display_name' => 'Create Referral Payout',
                'description' => 'Create payout for approved referral earnings'
            ],
        ];

        $allPermissions = array_merge($vendorPermissions, $referralEarningsPermissions);

        // Create permissions if they don't exist
        foreach ($allPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Assign all new permissions to super_admin and admin roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();

        if ($superAdmin) {
            $permissionIds = Permission::whereIn('name', array_column($allPermissions, 'name'))->pluck('id');
            $superAdmin->permissions()->syncWithoutDetaching($permissionIds);
        }

        if ($admin) {
            $permissionIds = Permission::whereIn('name', array_column($allPermissions, 'name'))->pluck('id');
            $admin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permissions
        Permission::whereIn('name', [
            'viewAny_vendor',
            'view_vendor',
            'create_vendor',
            'update_vendor',
            'delete_vendor',
            'approve_vendor',
            'viewAny_referral_earning',
            'view_referral_earning',
            'approve_referral_earning',
            'create_referral_payout',
        ])->delete();
    }
};
