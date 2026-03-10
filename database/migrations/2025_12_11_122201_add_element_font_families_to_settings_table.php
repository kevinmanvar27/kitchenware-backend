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
            if (!Schema::hasColumn('settings', 'h1_font_family')) {
                $table->string('h1_font_family')->default('Arial, sans-serif')->after('font_style');
            }
            if (!Schema::hasColumn('settings', 'h2_font_family')) {
                $table->string('h2_font_family')->default('Arial, sans-serif')->after('h1_font_family');
            }
            if (!Schema::hasColumn('settings', 'h3_font_family')) {
                $table->string('h3_font_family')->default('Arial, sans-serif')->after('h2_font_family');
            }
            if (!Schema::hasColumn('settings', 'h4_font_family')) {
                $table->string('h4_font_family')->default('Arial, sans-serif')->after('h3_font_family');
            }
            if (!Schema::hasColumn('settings', 'h5_font_family')) {
                $table->string('h5_font_family')->default('Arial, sans-serif')->after('h4_font_family');
            }
            if (!Schema::hasColumn('settings', 'h6_font_family')) {
                $table->string('h6_font_family')->default('Arial, sans-serif')->after('h5_font_family');
            }
            if (!Schema::hasColumn('settings', 'body_font_family')) {
                $table->string('body_font_family')->default('Arial, sans-serif')->after('h6_font_family');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'h1_font_family',
                'h2_font_family',
                'h3_font_family',
                'h4_font_family',
                'h5_font_family',
                'h6_font_family',
                'body_font_family',
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
