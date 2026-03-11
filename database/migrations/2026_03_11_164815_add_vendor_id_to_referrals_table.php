<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Vendor;
use App\Models\Referral;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // Add vendor_id column to link referral codes to vendors
            $table->unsignedBigInteger('vendor_id')->nullable()->after('referral_code');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->index('vendor_id');
        });
        
        // Update existing vendor referral codes to link them to their vendors
        $vendors = Vendor::whereNotNull('referral_code')->get();
        
        foreach ($vendors as $vendor) {
            Referral::where('referral_code', $vendor->referral_code)
                ->update(['vendor_id' => $vendor->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
