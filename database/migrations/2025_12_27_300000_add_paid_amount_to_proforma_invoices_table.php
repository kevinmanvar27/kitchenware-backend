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
        Schema::table('proforma_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('proforma_invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('proforma_invoices', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('proforma_invoices', 'paid_amount')) {
                $columns[] = 'paid_amount';
            }
            if (Schema::hasColumn('proforma_invoices', 'payment_status')) {
                $columns[] = 'payment_status';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
