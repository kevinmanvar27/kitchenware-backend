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
        // Create task management permissions
        $permissions = [
            [
                'name' => 'manage_tasks',
                'display_name' => 'Manage Tasks',
                'description' => 'Create, edit, and delete tasks'
            ],
            [
                'name' => 'view_tasks',
                'display_name' => 'View Tasks',
                'description' => 'View assigned tasks'
            ],
            [
                'name' => 'update_task_status',
                'display_name' => 'Update Task Status',
                'description' => 'Update task status and add comments'
            ],
            [
                'name' => 'verify_tasks',
                'display_name' => 'Verify Tasks',
                'description' => 'Verify completed tasks'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'description' => $permission['description']
                ]
            );
        }

        // Assign permissions to roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['manage_tasks', 'view_tasks', 'update_task_status', 'verify_tasks'])->pluck('id')
            );
        }

        $vendorRole = Role::where('name', 'vendor')->first();
        if ($vendorRole) {
            $vendorRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['view_tasks', 'update_task_status', 'verify_tasks'])->pluck('id')
            );
        }

        $vendorStaffRole = Role::where('name', 'vendor_staff')->first();
        if ($vendorStaffRole) {
            $vendorStaffRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['view_tasks', 'update_task_status'])->pluck('id')
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions
        Permission::whereIn('name', ['manage_tasks', 'view_tasks', 'update_task_status', 'verify_tasks'])->delete();
    }
};
