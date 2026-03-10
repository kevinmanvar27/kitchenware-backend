<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds profile_avatar field to vendor_customers table
     * to allow customers to upload their profile picture.
     */
    public function up(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_customers', 'profile_avatar')) {
                $table->string('profile_avatar')->nullable()->after('postal_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_customers', 'profile_avatar')) {
                $table->dropColumn('profile_avatar');
            }
        });
    }
};
