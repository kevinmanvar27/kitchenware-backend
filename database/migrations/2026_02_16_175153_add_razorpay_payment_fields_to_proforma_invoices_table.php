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
            // Add Razorpay payment fields only if they don't exist
            if (!Schema::hasColumn('proforma_invoices', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status')
                    ->comment('Payment method used (razorpay, cod, manual, etc.)');
            }
            
            if (!Schema::hasColumn('proforma_invoices', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->after('payment_method')
                    ->comment('Razorpay order ID for online payments');
            }
            
            if (!Schema::hasColumn('proforma_invoices', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id')
                    ->comment('Razorpay payment ID after successful payment');
            }
            
            if (!Schema::hasColumn('proforma_invoices', 'razorpay_signature')) {
                $table->string('razorpay_signature')->nullable()->after('razorpay_payment_id')
                    ->comment('Razorpay signature for payment verification');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proforma_invoices', function (Blueprint $table) {
            // Drop columns only if they exist
            $columnsToDrop = [];
            
            if (Schema::hasColumn('proforma_invoices', 'razorpay_signature')) {
                $columnsToDrop[] = 'razorpay_signature';
            }
            if (Schema::hasColumn('proforma_invoices', 'razorpay_payment_id')) {
                $columnsToDrop[] = 'razorpay_payment_id';
            }
            if (Schema::hasColumn('proforma_invoices', 'razorpay_order_id')) {
                $columnsToDrop[] = 'razorpay_order_id';
            }
            if (Schema::hasColumn('proforma_invoices', 'payment_method')) {
                $columnsToDrop[] = 'payment_method';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
