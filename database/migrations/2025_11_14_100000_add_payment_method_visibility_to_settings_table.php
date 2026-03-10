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
            // Add payment method visibility settings
            if (!Schema::hasColumn('settings', 'show_online_payment')) {
                $table->boolean('show_online_payment')->default(true);
            }
            if (!Schema::hasColumn('settings', 'show_cod_payment')) {
                $table->boolean('show_cod_payment')->default(true);
            }
            if (!Schema::hasColumn('settings', 'show_invoice_payment')) {
                $table->boolean('show_invoice_payment')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Remove payment method visibility settings
            $columns = [];
            if (Schema::hasColumn('settings', 'show_online_payment')) {
                $columns[] = 'show_online_payment';
            }
            if (Schema::hasColumn('settings', 'show_cod_payment')) {
                $columns[] = 'show_cod_payment';
            }
            if (Schema::hasColumn('settings', 'show_invoice_payment')) {
                $columns[] = 'show_invoice_payment';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};