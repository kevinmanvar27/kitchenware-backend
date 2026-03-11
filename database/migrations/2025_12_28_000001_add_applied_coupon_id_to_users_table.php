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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'applied_coupon_id')) {
                $table->unsignedBigInteger('applied_coupon_id')->nullable();
                $table->foreign('applied_coupon_id')->references('id')->on('coupons')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'applied_coupon_id')) {
                $table->dropForeign(['applied_coupon_id']);
                $table->dropColumn('applied_coupon_id');
            }
        });
    }
};
