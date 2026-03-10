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
        // Only create if vendors table exists (it's created later)
        if (!Schema::hasTable('vendors')) {
            return;
        }
        
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payouts');
    }
};