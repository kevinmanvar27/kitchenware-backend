<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create admin feature settings table
        Schema::create('admin_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('feature_key')->unique(); // e.g., 'products', 'invoices', 'leads'
            $table->string('feature_name'); // Display name
            $table->string('feature_description')->nullable(); // Description of the feature
            $table->string('feature_group')->default('general'); // Group for organization
            $table->boolean('is_enabled')->default(true); // Admin global toggle
            $table->boolean('allow_vendor_override')->default(true); // Can vendor enable if admin disabled?
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Create vendor feature settings table
        Schema::create('vendor_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('feature_key');
            $table->boolean('is_enabled')->default(true); // Vendor's preference
            $table->timestamps();
            
            $table->unique(['vendor_id', 'feature_key']);
        });

        // Seed default features
        $features = [
            // Catalog Management
            ['feature_key' => 'products', 'feature_name' => 'Products', 'feature_description' => 'Manage product catalog', 'feature_group' => 'catalog', 'sort_order' => 1],
            ['feature_key' => 'categories', 'feature_name' => 'Categories', 'feature_description' => 'Manage product categories', 'feature_group' => 'catalog', 'sort_order' => 2],
            ['feature_key' => 'attributes', 'feature_name' => 'Attributes', 'feature_description' => 'Manage product attributes and variations', 'feature_group' => 'catalog', 'sort_order' => 3],
            
            // Sales & Orders
            ['feature_key' => 'invoices', 'feature_name' => 'Invoices', 'feature_description' => 'Create and manage invoices', 'feature_group' => 'sales', 'sort_order' => 4],
            ['feature_key' => 'pending_bills', 'feature_name' => 'Pending Bills', 'feature_description' => 'Track pending payments', 'feature_group' => 'sales', 'sort_order' => 5],
            
            // Customer Management
            ['feature_key' => 'customers', 'feature_name' => 'Customers', 'feature_description' => 'Manage customer accounts', 'feature_group' => 'customers', 'sort_order' => 6],
            ['feature_key' => 'leads', 'feature_name' => 'Leads', 'feature_description' => 'Lead management and tracking', 'feature_group' => 'customers', 'sort_order' => 7],
            
            // Team Management
            ['feature_key' => 'staff', 'feature_name' => 'Staff', 'feature_description' => 'Manage staff members', 'feature_group' => 'team', 'sort_order' => 8],
            ['feature_key' => 'attendance', 'feature_name' => 'Attendance', 'feature_description' => 'Track staff attendance', 'feature_group' => 'team', 'sort_order' => 9],
            ['feature_key' => 'salary', 'feature_name' => 'Salary', 'feature_description' => 'Manage staff salaries', 'feature_group' => 'team', 'sort_order' => 10],
            
            // Marketing & Analytics
            ['feature_key' => 'coupons', 'feature_name' => 'Coupons', 'feature_description' => 'Create and manage discount coupons', 'feature_group' => 'marketing', 'sort_order' => 11],
            ['feature_key' => 'reports', 'feature_name' => 'Reports', 'feature_description' => 'View sales and business reports', 'feature_group' => 'marketing', 'sort_order' => 12],
            ['feature_key' => 'analytics', 'feature_name' => 'Analytics', 'feature_description' => 'Product and sales analytics', 'feature_group' => 'marketing', 'sort_order' => 13],
            ['feature_key' => 'push_notifications', 'feature_name' => 'Push Notifications', 'feature_description' => 'Send push notifications to customers', 'feature_group' => 'marketing', 'sort_order' => 14],
            
            // Content
            ['feature_key' => 'media', 'feature_name' => 'Media Library', 'feature_description' => 'Manage media files and images', 'feature_group' => 'content', 'sort_order' => 15],
            
            // Settings
            ['feature_key' => 'activity_logs', 'feature_name' => 'Activity Logs', 'feature_description' => 'View activity history', 'feature_group' => 'settings', 'sort_order' => 16],
        ];

        foreach ($features as $feature) {
            DB::table('admin_feature_settings')->insert(array_merge($feature, [
                'is_enabled' => true,
                'allow_vendor_override' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_feature_settings');
        Schema::dropIfExists('admin_feature_settings');
    }
};
