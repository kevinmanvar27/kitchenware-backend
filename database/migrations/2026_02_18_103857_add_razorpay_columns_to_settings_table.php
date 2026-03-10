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
        Schema::table('settings', function (Blueprint $table) {
            // Add Razorpay configuration columns only if they don't exist
            if (!Schema::hasColumn('settings', 'razorpay_key_id')) {
                $table->string('razorpay_key_id')->nullable();
            }
            if (!Schema::hasColumn('settings', 'razorpay_key_secret')) {
                $table->string('razorpay_key_secret')->nullable();
            }
            if (!Schema::hasColumn('settings', 'razorpay_enabled')) {
                $table->boolean('razorpay_enabled')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Drop columns only if they exist
            if (Schema::hasColumn('settings', 'razorpay_enabled')) {
                $table->dropColumn('razorpay_enabled');
            }
            // Note: We don't drop razorpay_key_id and razorpay_key_secret as they were 
            // created in an earlier migration (consolidated_settings_migration)
        });
    }
};
