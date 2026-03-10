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
        // Create activity log permission
        $permission = Permission::firstOrCreate(
            ['name' => 'viewAny_activity_log'],
            [
                'name' => 'viewAny_activity_log',
                'display_name' => 'View Activity Logs',
                'description' => 'Can view activity logs in admin panel',
            ]
        );

        // Assign permission to super_admin and admin roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$superAdminRole->permissions->contains($permission->id)) {
            $superAdminRole->permissions()->attach($permission->id);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$adminRole->permissions->contains($permission->id)) {
            $adminRole->permissions()->attach($permission->id);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permission from roles first
        $permission = Permission::where('name', 'viewAny_activity_log')->first();
        
        if ($permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};
