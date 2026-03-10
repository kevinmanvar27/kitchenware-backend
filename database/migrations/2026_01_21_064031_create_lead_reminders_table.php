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
        if (!Schema::hasTable('lead_reminders')) {
            Schema::create('lead_reminders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained()->onDelete('cascade');
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('reminder_at');
                $table->enum('status', ['pending', 'completed', 'dismissed'])->default('pending');
                $table->dateTime('completed_at')->nullable();
                $table->timestamps();
                
                // Index for efficient querying
                $table->index(['vendor_id', 'status', 'reminder_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_reminders');
    }
};
