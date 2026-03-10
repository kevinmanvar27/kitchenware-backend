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
        // Add vendor_id to leads table
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
            }
        });

        // Add vendor_id to salaries table
        Schema::table('salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('salaries', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
        });

        Schema::table('salaries', function (Blueprint $table) {
            if (Schema::hasColumn('salaries', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
        });
    }
};
