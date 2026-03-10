<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds vendor_customer_id to link invoices to vendor customers.
     */
    public function up(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_invoices', 'vendor_customer_id')) {
                $table->unsignedBigInteger('vendor_customer_id')->nullable()->after('vendor_id');
                
                $table->foreign('vendor_customer_id')
                    ->references('id')
                    ->on('vendor_customers')
                    ->onDelete('set null');
                
                $table->index('vendor_customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('proforma_invoices', 'vendor_customer_id')) {
                $table->dropForeign(['vendor_customer_id']);
                $table->dropIndex(['vendor_customer_id']);
                $table->dropColumn('vendor_customer_id');
            }
        });
    }
};
