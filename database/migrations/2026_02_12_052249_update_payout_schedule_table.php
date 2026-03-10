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
            $table->string('payout_time')->default('00:00')->after('day');
            
            // Rename min_amount to min_pending_amount for clarity
            if (Schema::hasColumn('payout_schedules', 'min_amount')) {
                $table->renameColumn('min_amount', 'min_pending_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payout_schedules', function (Blueprint $table) {
            $table->dropColumn('payout_time');
            
            if (Schema::hasColumn('payout_schedules', 'min_pending_amount')) {
                $table->renameColumn('min_pending_amount', 'min_amount');
            }
        });
    }
};