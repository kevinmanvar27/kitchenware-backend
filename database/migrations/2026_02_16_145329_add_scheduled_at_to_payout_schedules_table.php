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
        Schema::table('payout_schedules', function (Blueprint $table) {
            // Add scheduled_at column for calendar-based scheduling
            $table->dateTime('scheduled_at')->nullable()->after('payout_time')->comment('Specific date and time for payout execution');
            
            // Add last_run_at to track when the schedule was last executed
            $table->dateTime('last_run_at')->nullable()->after('scheduled_at')->comment('Last time this schedule was executed');
            
            // Make frequency, day, and payout_time nullable since we're using scheduled_at now
            $table->string('frequency')->nullable()->change();
            $table->string('day')->nullable()->change();
            $table->string('payout_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_schedules', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'last_run_at']);
            
            // Revert nullable changes (optional, may cause issues if data exists)
            // $table->string('frequency')->nullable(false)->change();
            // $table->string('day')->nullable(false)->change();
            // $table->string('payout_time')->nullable(false)->change();
        });
    }
};
