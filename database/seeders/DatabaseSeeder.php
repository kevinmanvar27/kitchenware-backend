<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed the admin user
        $this->call(AdminUserSeeder::class);
        
        // Seed the default settings
        $this->call(SettingsTableSeeder::class);
        
        // Seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);
        
        // Seed category permissions
        $this->call(CategoryPermissionSeeder::class);
        
        // Seed categories and subcategories
        $this->call(CategorySeeder::class);
        
        // Seed user groups
        $this->call(UserGroupSeeder::class);
        
        // Seed homepage content
        $this->call(HomePageSeeder::class);
        
        // Seed test products
        $this->call(TestProductSeeder::class);
        
        // Associate products with categories
        $this->call(CategoryProductSeeder::class);
    }
}