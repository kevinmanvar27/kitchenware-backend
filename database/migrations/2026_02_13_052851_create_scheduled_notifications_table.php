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
        // Skip if table already exists (was created manually or in previous run)
        if (Schema::hasTable('scheduled_notifications')) {
            return;
        }
        
        Schema::create('scheduled_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // Additional JSON data
            $table->enum('target_type', ['all', 'selected'])->default('all');
            $table->json('customer_ids')->nullable(); // For selected customers
            $table->dateTime('scheduled_at');
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('fail_count')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
            
            // Index for efficient querying of pending notifications
            $table->index(['status', 'scheduled_at']);
            $table->index(['vendor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_notifications');
    }
};
