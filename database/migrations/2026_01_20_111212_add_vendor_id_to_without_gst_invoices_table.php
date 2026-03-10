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
        Schema::table('without_gst_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('without_gst_invoices', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('user_id');
                $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
                $table->index('vendor_id');
            }
            if (!Schema::hasColumn('without_gst_invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('without_gst_invoices', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('without_gst_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('without_gst_invoices', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropIndex(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
            if (Schema::hasColumn('without_gst_invoices', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (Schema::hasColumn('without_gst_invoices', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
