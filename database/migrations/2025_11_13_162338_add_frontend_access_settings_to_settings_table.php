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
            // Frontend access permission settings
            if (!Schema::hasColumn('settings', 'frontend_access_permission')) {
                $table->string('frontend_access_permission')->default('open_for_all'); // open_for_all, registered_users_only, admin_approval_required
            }
            if (!Schema::hasColumn('settings', 'pending_approval_message')) {
                $table->text('pending_approval_message')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Remove frontend access permission settings
            $columns = [];
            if (Schema::hasColumn('settings', 'frontend_access_permission')) {
                $columns[] = 'frontend_access_permission';
            }
            if (Schema::hasColumn('settings', 'pending_approval_message')) {
                $columns[] = 'pending_approval_message';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};