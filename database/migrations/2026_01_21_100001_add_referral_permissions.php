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
        // Create referral permissions
        $permissions = [
            ['name' => 'viewAny_referral', 'display_name' => 'View All Referrals'],
            ['name' => 'view_referral', 'display_name' => 'View Referral'],
            ['name' => 'create_referral', 'display_name' => 'Create Referral'],
            ['name' => 'update_referral', 'display_name' => 'Update Referral'],
            ['name' => 'delete_referral', 'display_name' => 'Delete Referral'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }

        // Assign all referral permissions to super_admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $referralPermissions = Permission::whereIn('name', array_column($permissions, 'name'))->get();
            $superAdminRole->permissions()->syncWithoutDetaching($referralPermissions->pluck('id'));
        }
        
        // Assign view permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $viewPermissions = Permission::whereIn('name', ['viewAny_referral', 'view_referral'])->get();
            $adminRole->permissions()->syncWithoutDetaching($viewPermissions->pluck('id'));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove referral permissions
        $permissionNames = [
            'viewAny_referral',
            'view_referral',
            'create_referral',
            'update_referral',
            'delete_referral',
        ];

        Permission::whereIn('name', $permissionNames)->delete();
    }
};
