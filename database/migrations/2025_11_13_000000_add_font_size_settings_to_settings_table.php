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
            // Responsive Font Size Matrix
            // Desktop sizes
            if (!Schema::hasColumn('settings', 'desktop_h1_size')) {
                $table->integer('desktop_h1_size')->nullable()->default(36);
            }
            if (!Schema::hasColumn('settings', 'desktop_h2_size')) {
                $table->integer('desktop_h2_size')->nullable()->default(30);
            }
            if (!Schema::hasColumn('settings', 'desktop_h3_size')) {
                $table->integer('desktop_h3_size')->nullable()->default(24);
            }
            if (!Schema::hasColumn('settings', 'desktop_h4_size')) {
                $table->integer('desktop_h4_size')->nullable()->default(20);
            }
            if (!Schema::hasColumn('settings', 'desktop_h5_size')) {
                $table->integer('desktop_h5_size')->nullable()->default(18);
            }
            if (!Schema::hasColumn('settings', 'desktop_h6_size')) {
                $table->integer('desktop_h6_size')->nullable()->default(16);
            }
            if (!Schema::hasColumn('settings', 'desktop_body_size')) {
                $table->integer('desktop_body_size')->nullable()->default(16);
            }
            
            // Tablet sizes
            if (!Schema::hasColumn('settings', 'tablet_h1_size')) {
                $table->integer('tablet_h1_size')->nullable()->default(32);
            }
            if (!Schema::hasColumn('settings', 'tablet_h2_size')) {
                $table->integer('tablet_h2_size')->nullable()->default(28);
            }
            if (!Schema::hasColumn('settings', 'tablet_h3_size')) {
                $table->integer('tablet_h3_size')->nullable()->default(22);
            }
            if (!Schema::hasColumn('settings', 'tablet_h4_size')) {
                $table->integer('tablet_h4_size')->nullable()->default(18);
            }
            if (!Schema::hasColumn('settings', 'tablet_h5_size')) {
                $table->integer('tablet_h5_size')->nullable()->default(16);
            }
            if (!Schema::hasColumn('settings', 'tablet_h6_size')) {
                $table->integer('tablet_h6_size')->nullable()->default(14);
            }
            if (!Schema::hasColumn('settings', 'tablet_body_size')) {
                $table->integer('tablet_body_size')->nullable()->default(14);
            }
            
            // Mobile sizes
            if (!Schema::hasColumn('settings', 'mobile_h1_size')) {
                $table->integer('mobile_h1_size')->nullable()->default(28);
            }
            if (!Schema::hasColumn('settings', 'mobile_h2_size')) {
                $table->integer('mobile_h2_size')->nullable()->default(24);
            }
            if (!Schema::hasColumn('settings', 'mobile_h3_size')) {
                $table->integer('mobile_h3_size')->nullable()->default(20);
            }
            if (!Schema::hasColumn('settings', 'mobile_h4_size')) {
                $table->integer('mobile_h4_size')->nullable()->default(16);
            }
            if (!Schema::hasColumn('settings', 'mobile_h5_size')) {
                $table->integer('mobile_h5_size')->nullable()->default(14);
            }
            if (!Schema::hasColumn('settings', 'mobile_h6_size')) {
                $table->integer('mobile_h6_size')->nullable()->default(12);
            }
            if (!Schema::hasColumn('settings', 'mobile_body_size')) {
                $table->integer('mobile_body_size')->nullable()->default(12);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Remove Responsive Font Size Matrix fields
            $columns = [
                'desktop_h1_size',
                'desktop_h2_size',
                'desktop_h3_size',
                'desktop_h4_size',
                'desktop_h5_size',
                'desktop_h6_size',
                'desktop_body_size',
                'tablet_h1_size',
                'tablet_h2_size',
                'tablet_h3_size',
                'tablet_h4_size',
                'tablet_h5_size',
                'tablet_h6_size',
                'tablet_body_size',
                'mobile_h1_size',
                'mobile_h2_size',
                'mobile_h3_size',
                'mobile_h4_size',
                'mobile_h5_size',
                'mobile_h6_size',
                'mobile_body_size'
            ];
            
            $existingColumns = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};