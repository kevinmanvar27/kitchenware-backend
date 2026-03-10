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
        // Define additional missing permissions for proper permission-based access control
        $permissions = [
            // Attendance Management Permissions
            [
                'name' => 'viewAny_attendance',
                'display_name' => 'View Any Attendance',
                'description' => 'View all attendance records'
            ],
            [
                'name' => 'view_attendance',
                'display_name' => 'View Attendance',
                'description' => 'View a specific attendance record'
            ],
            [
                'name' => 'create_attendance',
                'display_name' => 'Create Attendance',
                'description' => 'Create new attendance records'
            ],
            [
                'name' => 'update_attendance',
                'display_name' => 'Update Attendance',
                'description' => 'Modify existing attendance records'
            ],
            [
                'name' => 'delete_attendance',
                'display_name' => 'Delete Attendance',
                'description' => 'Remove attendance records'
            ],
            
            // Salary Management Permissions
            [
                'name' => 'viewAny_salary',
                'display_name' => 'View Any Salary',
                'description' => 'View all salary records'
            ],
            [
                'name' => 'view_salary',
                'display_name' => 'View Salary',
                'description' => 'View a specific salary record'
            ],
            [
                'name' => 'create_salary',
                'display_name' => 'Create Salary',
                'description' => 'Create new salary records'
            ],
            [
                'name' => 'update_salary',
                'display_name' => 'Update Salary',
                'description' => 'Modify existing salary records'
            ],
            [
                'name' => 'delete_salary',
                'display_name' => 'Delete Salary',
                'description' => 'Remove salary records'
            ],
            
            // Coupon Management Permissions
            [
                'name' => 'viewAny_coupon',
                'display_name' => 'View Any Coupon',
                'description' => 'View all coupons'
            ],
            [
                'name' => 'view_coupon',
                'display_name' => 'View Coupon',
                'description' => 'View a specific coupon'
            ],
            [
                'name' => 'create_coupon',
                'display_name' => 'Create Coupon',
                'description' => 'Create new coupons'
            ],
            [
                'name' => 'update_coupon',
                'display_name' => 'Update Coupon',
                'description' => 'Modify existing coupons'
            ],
            [
                'name' => 'delete_coupon',
                'display_name' => 'Delete Coupon',
                'description' => 'Remove coupons'
            ],
            
            // Vendor Management Permissions
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
                'description' => 'Approve vendor applications'
            ],
            
            // Referral Management Permissions
            [
                'name' => 'viewAny_referral',
                'display_name' => 'View Any Referral',
                'description' => 'View all referrals'
            ],
            [
                'name' => 'view_referral',
                'display_name' => 'View Referral',
                'description' => 'View a specific referral'
            ],
            [
                'name' => 'create_referral',
                'display_name' => 'Create Referral',
                'description' => 'Create new referrals'
            ],
            [
                'name' => 'update_referral',
                'display_name' => 'Update Referral',
                'description' => 'Modify existing referrals'
            ],
            [
                'name' => 'delete_referral',
                'display_name' => 'Delete Referral',
                'description' => 'Remove referrals'
            ],
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'description' => $permissionData['description'],
                    'guard_name' => 'web'
                ]
            );
        }

        // Get all permissions
        $allPermissions = Permission::all();

        // Assign all permissions to super_admin and admin roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();

        if ($superAdminRole) {
            $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        if ($adminRole) {
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionNames = [
            'viewAny_attendance', 'view_attendance', 'create_attendance', 'update_attendance', 'delete_attendance',
            'viewAny_salary', 'view_salary', 'create_salary', 'update_salary', 'delete_salary',
            'viewAny_coupon', 'view_coupon', 'create_coupon', 'update_coupon', 'delete_coupon',
            'viewAny_vendor', 'view_vendor', 'create_vendor', 'update_vendor', 'delete_vendor', 'approve_vendor',
            'viewAny_referral', 'view_referral', 'create_referral', 'update_referral', 'delete_referral',
        ];

        Permission::whereIn('name', $permissionNames)->delete();
    }
};
