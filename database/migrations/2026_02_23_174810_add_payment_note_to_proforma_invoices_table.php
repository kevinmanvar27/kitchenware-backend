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
            // Add payment_note column if it doesn't exist
            if (!Schema::hasColumn('proforma_invoices', 'payment_note')) {
                $table->text('payment_note')->nullable()->after('payment_method')
                    ->comment('Notes about payment transactions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            // Drop payment_note column if it exists
            if (Schema::hasColumn('proforma_invoices', 'payment_note')) {
                $table->dropColumn('payment_note');
            }
        });
    }
};
