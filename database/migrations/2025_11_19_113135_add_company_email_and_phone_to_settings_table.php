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
            if (!Schema::hasColumn('settings', 'company_email')) {
                $table->string('company_email')->nullable()->after('address');
            }
            if (!Schema::hasColumn('settings', 'company_phone')) {
                $table->string('company_phone')->nullable()->after('company_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('settings', 'company_email')) {
                $columns[] = 'company_email';
            }
            if (Schema::hasColumn('settings', 'company_phone')) {
                $columns[] = 'company_phone';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};