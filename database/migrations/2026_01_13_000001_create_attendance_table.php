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
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->date('date');
                $table->enum('status', ['present', 'absent', 'half_day', 'leave', 'holiday'])->default('present');
                $table->time('check_in')->nullable();
                $table->time('check_out')->nullable();
                $table->decimal('working_hours', 5, 2)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                
                // Unique constraint to prevent duplicate attendance for same user on same date
                $table->unique(['user_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
