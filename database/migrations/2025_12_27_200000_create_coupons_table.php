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
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('description')->nullable();
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_value', 10, 2);
                $table->decimal('min_order_amount', 10, 2)->default(0);
                $table->decimal('max_discount_amount', 10, 2)->nullable(); // Cap for percentage discounts
                $table->integer('usage_limit')->nullable(); // Total times coupon can be used (null = unlimited)
                $table->integer('usage_count')->default(0); // Times already used
                $table->integer('per_user_limit')->default(1); // Uses per user
                $table->dateTime('valid_from')->nullable();
                $table->dateTime('valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Create coupon_usage table to track per-user usage
        if (!Schema::hasTable('coupon_usage')) {
            Schema::create('coupon_usage', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('proforma_invoice_id')->nullable()->constrained()->onDelete('set null');
                $table->decimal('discount_applied', 10, 2);
                $table->timestamps();

                $table->index(['coupon_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usage');
        Schema::dropIfExists('coupons');
    }
};
