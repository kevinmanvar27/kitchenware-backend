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
        // Create coupon permissions
        $permissions = [
            [
                'name' => 'viewAny_coupon',
                'display_name' => 'View All Coupons',
                'description' => 'Can view all coupons list',
            ],
            [
                'name' => 'view_coupon',
                'display_name' => 'View Coupon',
                'description' => 'Can view coupon details',
            ],
            [
                'name' => 'create_coupon',
                'display_name' => 'Create Coupon',
                'description' => 'Can create new coupons',
            ],
            [
                'name' => 'update_coupon',
                'display_name' => 'Update Coupon',
                'description' => 'Can update existing coupons',
            ],
            [
                'name' => 'delete_coupon',
                'display_name' => 'Delete Coupon',
                'description' => 'Can delete coupons',
            ],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[] = Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign all coupon permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($createdPermissions as $permission) {
                if (!$adminRole->permissions->contains($permission->id)) {
                    $adminRole->permissions()->attach($permission->id);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions from roles first
        $permissionNames = [
            'viewAny_coupon',
            'view_coupon',
            'create_coupon',
            'update_coupon',
            'delete_coupon',
        ];

        $permissions = Permission::whereIn('name', $permissionNames)->get();
        
        foreach ($permissions as $permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};
