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
        Schema::create('subscription_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained('user_subscriptions')->onDelete('cascade');
            $table->enum('reminder_type', ['7_days', '3_days', '1_day', 'expired'])->comment('Type of reminder sent');
            $table->timestamp('sent_at')->nullable()->comment('When the reminder was sent');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable()->comment('Error message if sending failed');
            $table->timestamps();
            
            // Index for efficient queries
            $table->index(['vendor_id', 'reminder_type', 'status']);
            $table->index(['subscription_id', 'reminder_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_reminders');
    }
};
