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
        Schema::create('payout_schedules', function (Blueprint $table) {
            $table->id();
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly'])->default('weekly');
            $table->string('day')->comment('Day of week (1-7) or day of month (1-31) or "last"');
            $table->decimal('min_amount', 10, 2)->default(100.00)->comment('Minimum amount required for automatic payout');
            $table->enum('payout_mode', ['IMPS', 'NEFT', 'RTGS', 'UPI'])->default('IMPS');
            $table->boolean('enabled')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_schedules');
    }
};
