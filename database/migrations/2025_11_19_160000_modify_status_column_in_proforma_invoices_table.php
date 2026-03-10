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
        // Only modify if status column exists and is tinyInteger type
        if (Schema::hasColumn('proforma_invoices', 'status')) {
            Schema::table('proforma_invoices', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('proforma_invoices', function (Blueprint $table) {
                $table->enum('status', ['Draft', 'Approved', 'Dispatch', 'Out for Delivery', 'Delivered', 'Return'])
                      ->default('Draft')
                      ->after('invoice_data');
            });
        } else {
            Schema::table('proforma_invoices', function (Blueprint $table) {
                $table->enum('status', ['Draft', 'Approved', 'Dispatch', 'Out for Delivery', 'Delivered', 'Return'])
                      ->default('Draft')
                      ->after('invoice_data');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('proforma_invoices', 'status')) {
            Schema::table('proforma_invoices', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            
            Schema::table('proforma_invoices', function (Blueprint $table) {
                $table->tinyInteger('status')->default(0)->after('invoice_data');
            });
        }
    }
};