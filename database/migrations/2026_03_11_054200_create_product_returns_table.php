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
        Schema::create('product_returns', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // For frontend users
            $table->foreignId('vendor_customer_id')->nullable()->constrained()->onDelete('cascade'); // For app users
            $table->foreignId('proforma_invoice_id')->constrained()->onDelete('cascade');
            
            // Return identification
            $table->string('return_number')->unique();
            
            // Return items (JSON structure)
            $table->json('return_items');
            
            // Return details
            $table->decimal('return_amount', 10, 2);
            $table->enum('return_type', ['full', 'partial'])->default('partial');
            $table->string('reason_category');
            $table->text('reason_description')->nullable();
            
            // Images uploaded
            $table->json('images')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'pickup_scheduled',
                'picked_up',
                'received',
                'inspected',
                'refund_processing',
                'completed',
                'cancelled'
            ])->default('pending');
            
            // Vendor response
            $table->text('vendor_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            // Refund details
            $table->enum('refund_method', ['wallet', 'original_method', 'bank_transfer', 'cash'])->nullable();
            $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('refund_reference')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->timestamp('refund_completed_at')->nullable();
            
            // Pickup details
            $table->text('pickup_address')->nullable();
            $table->string('pickup_contact')->nullable();
            $table->timestamp('pickup_scheduled_at')->nullable();
            
            // Metadata
            $table->string('device_type')->nullable(); // android/ios/web
            $table->string('app_version')->nullable();
            $table->ipAddress('ip_address')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['vendor_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['vendor_customer_id', 'status']);
            $table->index('return_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_returns');
    }
};
