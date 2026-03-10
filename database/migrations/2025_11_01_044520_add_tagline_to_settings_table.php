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
        if (!Schema::hasColumn('settings', 'tagline')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('tagline')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('settings', 'tagline')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('tagline');
            });
        }
    }
};
