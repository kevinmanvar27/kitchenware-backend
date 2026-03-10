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
            $table->string('razorpayx_key_id')->nullable()->after('razorpay_key_secret');
            $table->string('razorpayx_key_secret')->nullable()->after('razorpayx_key_id');
            $table->string('razorpayx_account_number')->nullable()->after('razorpayx_key_secret');
            $table->string('razorpayx_webhook_secret')->nullable()->after('razorpayx_account_number');
            $table->enum('razorpayx_mode', ['test', 'live'])->default('test')->after('razorpayx_webhook_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'razorpayx_key_id',
                'razorpayx_key_secret',
                'razorpayx_account_number',
                'razorpayx_webhook_secret',
                'razorpayx_mode',
            ]);
        });
    }
};
