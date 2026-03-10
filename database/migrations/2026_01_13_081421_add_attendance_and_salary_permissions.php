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
        // Create Attendance Management Permissions
        $attendancePermissions = [
            [
                'name' => 'viewAny_attendance',
                'display_name' => 'View Any Attendance',
                'description' => 'View all attendance records and reports'
            ],
            [
                'name' => 'view_attendance',
                'display_name' => 'View Attendance',
                'description' => 'View a specific attendance record'
            ],
            [
                'name' => 'create_attendance',
                'display_name' => 'Create Attendance',
                'description' => 'Create new attendance records (mark attendance)'
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
        ];

        // Create Salary Management Permissions
        $salaryPermissions = [
            [
                'name' => 'viewAny_salary',
                'display_name' => 'View Any Salary',
                'description' => 'View all salary records and payroll'
            ],
            [
                'name' => 'view_salary',
                'display_name' => 'View Salary',
                'description' => 'View a specific salary record'
            ],
            [
                'name' => 'create_salary',
                'display_name' => 'Create Salary',
                'description' => 'Create new salary configurations'
            ],
            [
                'name' => 'update_salary',
                'display_name' => 'Update Salary',
                'description' => 'Modify existing salary records and process payments'
            ],
            [
                'name' => 'delete_salary',
                'display_name' => 'Delete Salary',
                'description' => 'Remove salary records'
            ],
        ];

        $allPermissions = array_merge($attendancePermissions, $salaryPermissions);

        // Create permissions if they don't exist
        $createdPermissions = [];
        foreach ($allPermissions as $permissionData) {
            $createdPermissions[] = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Assign all permissions to super_admin and admin roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();

        $permissionIds = collect($createdPermissions)->pluck('id')->toArray();

        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching($permissionIds);
        }

        if ($admin) {
            $admin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions from roles first
        $permissionNames = [
            'viewAny_attendance',
            'view_attendance',
            'create_attendance',
            'update_attendance',
            'delete_attendance',
            'viewAny_salary',
            'view_salary',
            'create_salary',
            'update_salary',
            'delete_salary',
        ];

        $permissions = Permission::whereIn('name', $permissionNames)->get();

        foreach ($permissions as $permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};
