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
        Schema::table('referrals', function (Blueprint $table) {
            // Add phone number field
            if (!Schema::hasColumn('referrals', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->after('name');
            }
            
            // Add amount field for referral reward/commission
            if (!Schema::hasColumn('referrals', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('phone_number');
            }
            
            // Add payment status field to manage referral payments
            if (!Schema::hasColumn('referrals', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'cancelled'])->default('pending')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $columns = ['phone_number', 'amount', 'payment_status'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('referrals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
