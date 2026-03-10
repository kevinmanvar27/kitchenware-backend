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
            if (!Schema::hasColumn('settings', 'address')) {
                $table->text('address')->nullable()->after('footer_text');
            }
            if (!Schema::hasColumn('settings', 'gst_number')) {
                $table->string('gst_number')->nullable()->after('address');
            }
            if (!Schema::hasColumn('settings', 'authorized_signatory')) {
                $table->string('authorized_signatory')->nullable()->after('gst_number');
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
            if (Schema::hasColumn('settings', 'address')) {
                $columns[] = 'address';
            }
            if (Schema::hasColumn('settings', 'gst_number')) {
                $columns[] = 'gst_number';
            }
            if (Schema::hasColumn('settings', 'authorized_signatory')) {
                $columns[] = 'authorized_signatory';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};