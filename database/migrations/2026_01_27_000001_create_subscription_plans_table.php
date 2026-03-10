<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create subscription_plans table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly', 'lifetime'])->default('monthly');
            $table->integer('duration_days')->default(30);
            $table->json('features')->nullable();
            $table->integer('max_products')->nullable()->comment('-1 for unlimited');
            $table->integer('max_vendors')->nullable()->comment('-1 for unlimited');
            $table->integer('max_customers')->nullable()->comment('-1 for unlimited');
            $table->integer('max_invoices_per_month')->nullable()->comment('-1 for unlimited');
            $table->integer('storage_limit_mb')->nullable()->comment('-1 for unlimited');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('trial_days')->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        // Create user_subscriptions table
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->enum('status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->boolean('auto_renew')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index('status');
        });

        // Insert default subscription plans
        \DB::table('subscription_plans')->insert([
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started with basic features',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'features' => json_encode([
                    'Up to 50 products',
                    'Basic analytics',
                    'Email support',
                    '1 vendor account',
                    '100 customers',
                ]),
                'max_products' => 50,
                'max_vendors' => 1,
                'max_customers' => 100,
                'max_invoices_per_month' => 50,
                'storage_limit_mb' => 500,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'trial_days' => 0,
                'discount_percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Great for small businesses looking to grow',
                'price' => 999,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'features' => json_encode([
                    'Up to 500 products',
                    'Advanced analytics',
                    'Priority email support',
                    '3 vendor accounts',
                    '500 customers',
                    'Invoice generation',
                    'Custom branding',
                ]),
                'max_products' => 500,
                'max_vendors' => 3,
                'max_customers' => 500,
                'max_invoices_per_month' => 200,
                'storage_limit_mb' => 2048,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
                'trial_days' => 14,
                'discount_percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Best for growing businesses with advanced needs',
                'price' => 2499,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'features' => json_encode([
                    'Unlimited products',
                    'Full analytics suite',
                    '24/7 phone & email support',
                    '10 vendor accounts',
                    'Unlimited customers',
                    'Advanced invoice features',
                    'Custom branding',
                    'API access',
                    'Multi-currency support',
                ]),
                'max_products' => -1,
                'max_vendors' => 10,
                'max_customers' => -1,
                'max_invoices_per_month' => -1,
                'storage_limit_mb' => 10240,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
                'trial_days' => 14,
                'discount_percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Complete solution for large organizations',
                'price' => 4999,
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'features' => json_encode([
                    'Everything in Professional',
                    'Unlimited vendors',
                    'Dedicated account manager',
                    'Custom integrations',
                    'White-label solution',
                    'SLA guarantee',
                    'On-premise deployment option',
                    'Advanced security features',
                    'Custom reporting',
                ]),
                'max_products' => -1,
                'max_vendors' => -1,
                'max_customers' => -1,
                'max_invoices_per_month' => -1,
                'storage_limit_mb' => -1,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'trial_days' => 30,
                'discount_percentage' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
