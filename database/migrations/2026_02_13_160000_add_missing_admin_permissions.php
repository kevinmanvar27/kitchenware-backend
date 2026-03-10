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
        // Define all missing permissions for proper permission-based access control
        $permissions = [
            // Settings Management
            [
                'name' => 'manage_settings',
                'display_name' => 'Manage Settings',
                'description' => 'Access and modify application settings'
            ],
            
            // User Management Permissions
            [
                'name' => 'viewAny_user',
                'display_name' => 'View Any User',
                'description' => 'View all users'
            ],
            [
                'name' => 'view_user',
                'display_name' => 'View User',
                'description' => 'View a specific user'
            ],
            [
                'name' => 'create_user',
                'display_name' => 'Create User',
                'description' => 'Create new users'
            ],
            [
                'name' => 'update_user',
                'display_name' => 'Update User',
                'description' => 'Modify existing users'
            ],
            [
                'name' => 'delete_user',
                'display_name' => 'Delete User',
                'description' => 'Remove users'
            ],
            
            // Staff Management Permissions
            [
                'name' => 'viewAny_staff',
                'display_name' => 'View Any Staff',
                'description' => 'View all staff members'
            ],
            [
                'name' => 'view_staff',
                'display_name' => 'View Staff',
                'description' => 'View a specific staff member'
            ],
            [
                'name' => 'create_staff',
                'display_name' => 'Create Staff',
                'description' => 'Create new staff members'
            ],
            [
                'name' => 'update_staff',
                'display_name' => 'Update Staff',
                'description' => 'Modify existing staff members'
            ],
            [
                'name' => 'delete_staff',
                'display_name' => 'Delete Staff',
                'description' => 'Remove staff members'
            ],
            
            // User Group Management Permissions
            [
                'name' => 'viewAny_user_group',
                'display_name' => 'View Any User Group',
                'description' => 'View all user groups'
            ],
            [
                'name' => 'view_user_group',
                'display_name' => 'View User Group',
                'description' => 'View a specific user group'
            ],
            [
                'name' => 'create_user_group',
                'display_name' => 'Create User Group',
                'description' => 'Create new user groups'
            ],
            [
                'name' => 'update_user_group',
                'display_name' => 'Update User Group',
                'description' => 'Modify existing user groups'
            ],
            [
                'name' => 'delete_user_group',
                'display_name' => 'Delete User Group',
                'description' => 'Remove user groups'
            ],
            
            // Subscription Plan Management Permissions
            [
                'name' => 'viewAny_subscription_plan',
                'display_name' => 'View Any Subscription Plan',
                'description' => 'View all subscription plans'
            ],
            [
                'name' => 'view_subscription_plan',
                'display_name' => 'View Subscription Plan',
                'description' => 'View a specific subscription plan'
            ],
            [
                'name' => 'create_subscription_plan',
                'display_name' => 'Create Subscription Plan',
                'description' => 'Create new subscription plans'
            ],
            [
                'name' => 'update_subscription_plan',
                'display_name' => 'Update Subscription Plan',
                'description' => 'Modify existing subscription plans'
            ],
            [
                'name' => 'delete_subscription_plan',
                'display_name' => 'Delete Subscription Plan',
                'description' => 'Remove subscription plans'
            ],
            
            // Notification Management Permissions
            [
                'name' => 'viewAny_notification',
                'display_name' => 'View Any Notification',
                'description' => 'View all notifications'
            ],
            [
                'name' => 'send_notification',
                'display_name' => 'Send Notification',
                'description' => 'Send push notifications to users'
            ],
            [
                'name' => 'manage_firebase',
                'display_name' => 'Manage Firebase',
                'description' => 'Manage Firebase configuration and test notifications'
            ],
            
            // Product Attribute Management Permissions
            [
                'name' => 'viewAny_attribute',
                'display_name' => 'View Any Attribute',
                'description' => 'View all product attributes'
            ],
            [
                'name' => 'view_attribute',
                'display_name' => 'View Attribute',
                'description' => 'View a specific product attribute'
            ],
            [
                'name' => 'create_attribute',
                'display_name' => 'Create Attribute',
                'description' => 'Create new product attributes'
            ],
            [
                'name' => 'update_attribute',
                'display_name' => 'Update Attribute',
                'description' => 'Modify existing product attributes'
            ],
            [
                'name' => 'delete_attribute',
                'display_name' => 'Delete Attribute',
                'description' => 'Remove product attributes'
            ],
            
            // Product Analytics Permissions
            [
                'name' => 'viewAny_product_analytics',
                'display_name' => 'View Product Analytics',
                'description' => 'View product analytics and reports'
            ],
            
            // Feature Settings Permissions
            [
                'name' => 'manage_feature_settings',
                'display_name' => 'Manage Feature Settings',
                'description' => 'Manage vendor feature settings'
            ],
            
            // Database Management Permissions
            [
                'name' => 'manage_database',
                'display_name' => 'Manage Database',
                'description' => 'Clean and export database'
            ],
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Get super_admin and admin roles and assign all new permissions
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        
        if ($superAdminRole || $adminRole) {
            $allPermissions = Permission::all()->pluck('id')->toArray();
            
            if ($superAdminRole) {
                $superAdminRole->permissions()->sync($allPermissions);
            }
            
            if ($adminRole) {
                $adminRole->permissions()->sync($allPermissions);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionNames = [
            'manage_settings',
            'viewAny_user', 'view_user', 'create_user', 'update_user', 'delete_user',
            'viewAny_staff', 'view_staff', 'create_staff', 'update_staff', 'delete_staff',
            'viewAny_user_group', 'view_user_group', 'create_user_group', 'update_user_group', 'delete_user_group',
            'viewAny_subscription_plan', 'view_subscription_plan', 'create_subscription_plan', 'update_subscription_plan', 'delete_subscription_plan',
            'viewAny_notification', 'send_notification', 'manage_firebase',
            'viewAny_attribute', 'view_attribute', 'create_attribute', 'update_attribute', 'delete_attribute',
            'viewAny_product_analytics',
            'manage_feature_settings',
            'manage_database',
        ];

        Permission::whereIn('name', $permissionNames)->delete();
    }
};
