<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds device_token field to vendor_customers table
     * to support push notifications for vendor customers via the Flutter app.
     */
    public function up(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_customers', 'device_token')) {
                $table->string('device_token')->nullable()->after('profile_avatar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_customers', 'device_token')) {
                $table->dropColumn('device_token');
            }
        });
    }
};
