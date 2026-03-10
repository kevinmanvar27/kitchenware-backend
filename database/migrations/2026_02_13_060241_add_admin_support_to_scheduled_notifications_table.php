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
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            // Add admin-specific fields only if they don't exist
            if (!Schema::hasColumn('scheduled_notifications', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('customer_ids');
            }
            if (!Schema::hasColumn('scheduled_notifications', 'user_group_id')) {
                $table->unsignedBigInteger('user_group_id')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('scheduled_notifications', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('scheduled_notifications', 'user_group_id')) {
                $table->dropColumn('user_group_id');
            }
        });
    }
};
