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
        Schema::dropIfExists('vendor_reviews');
    }
};