<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class LeadPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Lead Management Permissions
        $permissions = [
            [
                'name' => 'viewAny_lead',
                'display_name' => 'View Any Lead',
                'description' => 'View all leads'
            ],
            [
                'name' => 'view_lead',
                'display_name' => 'View Lead',
                'description' => 'View a specific lead'
            ],
            [
                'name' => 'create_lead',
                'display_name' => 'Create Lead',
                'description' => 'Create new leads'
            ],
            [
                'name' => 'update_lead',
                'display_name' => 'Update Lead',
                'description' => 'Modify existing leads'
            ],
            [
                'name' => 'delete_lead',
                'display_name' => 'Delete Lead',
                'description' => 'Remove leads (soft delete)'
            ],
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Assign all lead permissions to super_admin and admin roles
        $leadPermissions = Permission::whereIn('name', [
            'viewAny_lead',
            'view_lead',
            'create_lead',
            'update_lead',
            'delete_lead'
        ])->get();

        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();

        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching($leadPermissions);
        }

        if ($admin) {
            $admin->permissions()->syncWithoutDetaching($leadPermissions);
        }

        $this->command->info('Lead permissions created and assigned successfully!');
    }
}
