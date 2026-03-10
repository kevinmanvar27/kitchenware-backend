<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds authentication fields to vendor_customers table
     * to allow vendors to create customers with login credentials.
     * Customers created by a vendor can only see that vendor's products.
     */
    public function up(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_customers', 'name')) {
                $table->string('name')->after('user_id')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'email')) {
                $table->string('email')->after('name')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'password')) {
                $table->string('password')->after('email')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'mobile_number')) {
                $table->string('mobile_number', 20)->after('password')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'address')) {
                $table->text('address')->after('mobile_number')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'city')) {
                $table->string('city', 100)->after('address')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'state')) {
                $table->string('state', 100)->after('city')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'postal_code')) {
                $table->string('postal_code', 20)->after('state')->nullable();
            }
            if (!Schema::hasColumn('vendor_customers', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('postal_code');
            }
            if (!Schema::hasColumn('vendor_customers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('discount_percentage');
            }
            if (!Schema::hasColumn('vendor_customers', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('vendor_customers', 'remember_token')) {
                $table->rememberToken()->after('last_login_at');
            }
            
            // Make user_id nullable since vendor can create customer without linking to users table
            // This is handled in the base migration now
        });
        
        // Add unique constraint for email per vendor (same email can exist for different vendors)
        // Check if the index doesn't already exist
        $indexExists = collect(Schema::getIndexes('vendor_customers'))->contains(function ($index) {
            return $index['name'] === 'vendor_customer_email_unique';
        });
        
        if (!$indexExists) {
            Schema::table('vendor_customers', function (Blueprint $table) {
                $table->unique(['vendor_id', 'email'], 'vendor_customer_email_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            // Check if index exists before dropping
            $indexExists = collect(Schema::getIndexes('vendor_customers'))->contains(function ($index) {
                return $index['name'] === 'vendor_customer_email_unique';
            });
            
            if ($indexExists) {
                $table->dropUnique('vendor_customer_email_unique');
            }
            
            $columns = [
                'name',
                'email', 
                'password',
                'mobile_number',
                'address',
                'city',
                'state',
                'postal_code',
                'discount_percentage',
                'is_active',
                'last_login_at',
                'remember_token'
            ];
            
            $existingColumns = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('vendor_customers', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
