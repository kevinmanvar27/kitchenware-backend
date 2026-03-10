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
        Schema::table('referral_users', function (Blueprint $table) {
            // Add payment tracking fields per referred user
            if (!Schema::hasColumn('referral_users', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid'])->default('pending')->after('notes');
            }
            if (!Schema::hasColumn('referral_users', 'payment_amount')) {
                $table->decimal('payment_amount', 10, 2)->default(0)->after('payment_status');
            }
            if (!Schema::hasColumn('referral_users', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_amount');
            }
            if (!Schema::hasColumn('referral_users', 'payment_notes')) {
                $table->string('payment_notes')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_users', function (Blueprint $table) {
            $columns = ['payment_status', 'payment_amount', 'paid_at', 'payment_notes'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('referral_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
