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
        if (!Schema::hasTable('salary_payments')) {
            Schema::create('salary_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('month'); // 1-12
                $table->integer('year');
                
                // Attendance summary for the month
                $table->integer('total_days')->default(0);
                $table->integer('present_days')->default(0);
                $table->integer('absent_days')->default(0);
                $table->integer('half_days')->default(0);
                $table->integer('leave_days')->default(0);
                $table->integer('holiday_days')->default(0);
                
                // Salary calculation breakdown
                $table->decimal('base_salary', 12, 2)->default(0);
                $table->decimal('daily_rate', 10, 2)->default(0);
                $table->decimal('earned_salary', 12, 2)->default(0); // Based on attendance
                $table->decimal('deductions', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->decimal('net_salary', 12, 2)->default(0);
                
                // Payment details
                $table->decimal('paid_amount', 12, 2)->default(0);
                $table->decimal('pending_amount', 12, 2)->default(0);
                $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
                $table->date('payment_date')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
                
                $table->text('notes')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                
                // Unique constraint for one payment record per user per month
                $table->unique(['user_id', 'month', 'year']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
