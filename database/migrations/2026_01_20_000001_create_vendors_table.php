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
        if (!Schema::hasTable('vendors')) {
            Schema::create('vendors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('store_name');
                $table->string('store_slug')->unique();
                $table->text('store_description')->nullable();
                $table->string('store_logo')->nullable();
                $table->string('store_banner')->nullable();
                $table->string('business_email')->nullable();
                $table->string('business_phone')->nullable();
                $table->text('business_address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('gst_number')->nullable();
                $table->string('pan_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_ifsc_code')->nullable();
                $table->string('bank_account_holder_name')->nullable();
                $table->decimal('commission_rate', 5, 2)->default(0.00); // Commission percentage
                $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->boolean('is_featured')->default(false);
                $table->integer('priority')->default(0);
                $table->json('social_links')->nullable();
                $table->json('store_settings')->nullable();
                $table->timestamps();
            });
        }

        // Add vendor_id to products table
        if (!Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->onDelete('cascade');
            });
        }

        // Add vendor_id to categories table (for vendor-specific categories)
        if (!Schema::hasColumn('categories', 'vendor_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->onDelete('cascade');
            });
        }

        // Create vendor permissions table
        if (!Schema::hasTable('vendor_permissions')) {
            Schema::create('vendor_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['vendor_id', 'permission_id']);
            });
        }

        // Create vendor staff table (for vendors to have their own staff)
        if (!Schema::hasTable('vendor_staff')) {
            Schema::create('vendor_staff', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('role')->default('staff'); // staff, manager, etc.
                $table->json('permissions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->unique(['vendor_id', 'user_id']);
            });
        }
        
        // Create vendor_followers table (was supposed to be created earlier but vendors table didn't exist)
        if (!Schema::hasTable('vendor_followers')) {
            Schema::create('vendor_followers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['vendor_id', 'user_id']);
            });
        }
        
        // Create vendor_payouts table (was supposed to be created earlier but vendors table didn't exist)
        if (!Schema::hasTable('vendor_payouts')) {
            Schema::create('vendor_payouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
                $table->json('bank_details')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                
                $table->index(['vendor_id', 'status']);
                $table->index('requested_at');
            });
        }
        
        // Create vendor_reviews table (was supposed to be created earlier but vendors table didn't exist)
        if (!Schema::hasTable('vendor_reviews')) {
            Schema::create('vendor_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->nullable()->constrained('proforma_invoices')->onDelete('set null');
                $table->tinyInteger('rating')->unsigned();
                $table->string('title')->nullable();
                $table->text('comment')->nullable();
                $table->text('vendor_reply')->nullable();
                $table->timestamp('vendor_replied_at')->nullable();
                $table->boolean('is_verified_purchase')->default(false);
                $table->boolean('is_approved')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
                
                $table->index(['vendor_id', 'is_approved']);
                $table->index(['user_id', 'vendor_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'vendor_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }

        if (Schema::hasColumn('categories', 'vendor_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }

        Schema::dropIfExists('vendor_reviews');
        Schema::dropIfExists('vendor_payouts');
        Schema::dropIfExists('vendor_followers');
        Schema::dropIfExists('vendor_staff');
        Schema::dropIfExists('vendor_permissions');
        Schema::dropIfExists('vendors');
    }
};
