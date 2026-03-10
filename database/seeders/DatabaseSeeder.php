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
        // Seed the admin user first
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
        
        // Create test user only if it doesn't exist
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
    }
}