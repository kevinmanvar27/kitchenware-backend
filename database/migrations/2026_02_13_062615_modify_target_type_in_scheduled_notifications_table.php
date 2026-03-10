<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change target_type from ENUM to VARCHAR to support more values
        // This allows: 'all', 'selected' (vendor), 'user', 'group', 'all_users' (admin)
        DB::statement("ALTER TABLE scheduled_notifications MODIFY COLUMN target_type VARCHAR(50) DEFAULT 'all'");
        
        // Make vendor_id nullable for admin notifications
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->change();
        });
        
        // Add is_admin_notification column if it doesn't exist
        if (!Schema::hasColumn('scheduled_notifications', 'is_admin_notification')) {
            Schema::table('scheduled_notifications', function (Blueprint $table) {
                $table->boolean('is_admin_notification')->default(false)->after('vendor_id');
            });
        }
        
        // Add created_by column if it doesn't exist
        if (!Schema::hasColumn('scheduled_notifications', 'created_by')) {
            Schema::table('scheduled_notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('sent_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert target_type back to ENUM (only if no admin values exist)
        DB::statement("ALTER TABLE scheduled_notifications MODIFY COLUMN target_type ENUM('all', 'selected') DEFAULT 'all'");
        
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('scheduled_notifications', 'is_admin_notification')) {
                $table->dropColumn('is_admin_notification');
            }
            if (Schema::hasColumn('scheduled_notifications', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};
