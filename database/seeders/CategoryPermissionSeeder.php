<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class CategoryPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create category permissions
        $permissions = [
            [
                'name' => 'viewAny_category',
                'display_name' => 'View Any Category',
                'description' => 'Allow user to view any category'
            ],
            [
                'name' => 'view_category',
                'display_name' => 'View Category',
                'description' => 'Allow user to view a specific category'
            ],
            [
                'name' => 'create_category',
                'display_name' => 'Create Category',
                'description' => 'Allow user to create categories'
            ],
            [
                'name' => 'update_category',
                'display_name' => 'Update Category',
                'description' => 'Allow user to update categories'
            ],
            [
                'name' => 'delete_category',
                'display_name' => 'Delete Category',
                'description' => 'Allow user to delete categories'
            ],
            [
                'name' => 'viewAny_subcategory',
                'display_name' => 'View Any Subcategory',
                'description' => 'Allow user to view any subcategory'
            ],
            [
                'name' => 'view_subcategory',
                'display_name' => 'View Subcategory',
                'description' => 'Allow user to view a specific subcategory'
            ],
            [
                'name' => 'create_subcategory',
                'display_name' => 'Create Subcategory',
                'description' => 'Allow user to create subcategories'
            ],
            [
                'name' => 'update_subcategory',
                'display_name' => 'Update Subcategory',
                'description' => 'Allow user to update subcategories'
            ],
            [
                'name' => 'delete_subcategory',
                'display_name' => 'Delete Subcategory',
                'description' => 'Allow user to delete subcategories'
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'display_name' => $permissionData['display_name'],
                    'description' => $permissionData['description']
                ]
            );
        }

        // Assign category permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permissionData) {
                $permission = Permission::where('name', $permissionData['name'])->first();
                if ($permission && !$adminRole->permissions()->where('permission_id', $permission->id)->exists()) {
                    $adminRole->permissions()->attach($permission->id);
                }
            }
        }

        // Assign category permissions to super_admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            foreach ($permissions as $permissionData) {
                $permission = Permission::where('name', $permissionData['name'])->first();
                if ($permission && !$superAdminRole->permissions()->where('permission_id', $permission->id)->exists()) {
                    $superAdminRole->permissions()->attach($permission->id);
                }
            }
        }
    }
}