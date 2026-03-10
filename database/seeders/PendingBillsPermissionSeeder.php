<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PendingBillsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder adds the manage_pending_bills permission without resetting existing permissions.
     * Run with: php artisan db:seed --class=PendingBillsPermissionSeeder
     */
    public function run(): void
    {
        // Create the pending bills permission if it doesn't exist
        $permission = Permission::firstOrCreate(
            ['name' => 'manage_pending_bills'],
            [
                'display_name' => 'Manage Pending Bills',
                'description' => 'View pending bills, add payments, and view user-wise summaries'
            ]
        );

        $this->command->info('Permission "manage_pending_bills" created/found.');

        // Assign permission to super_admin and admin roles
        $roles = Role::whereIn('name', ['super_admin', 'admin'])->get();
        
        foreach ($roles as $role) {
            if (!$role->permissions->contains($permission->id)) {
                $role->permissions()->attach($permission->id);
                $this->command->info("Permission assigned to role: {$role->name}");
            } else {
                $this->command->info("Permission already assigned to role: {$role->name}");
            }
        }

        $this->command->info('Pending Bills permission seeder completed successfully!');
    }
}
