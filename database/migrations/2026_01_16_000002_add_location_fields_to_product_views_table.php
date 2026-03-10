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
        Schema::table('product_views', function (Blueprint $table) {
            // Add missing location columns if they don't exist
            if (!Schema::hasColumn('product_views', 'country_code')) {
                $table->string('country_code', 10)->nullable()->after('country');
            }
            if (!Schema::hasColumn('product_views', 'region')) {
                $table->string('region', 100)->nullable()->after('country_code');
            }
            if (!Schema::hasColumn('product_views', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('city');
            }
            if (!Schema::hasColumn('product_views', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_views', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('product_views', 'country_code')) {
                $columns[] = 'country_code';
            }
            if (Schema::hasColumn('product_views', 'region')) {
                $columns[] = 'region';
            }
            if (Schema::hasColumn('product_views', 'latitude')) {
                $columns[] = 'latitude';
            }
            if (Schema::hasColumn('product_views', 'longitude')) {
                $columns[] = 'longitude';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
