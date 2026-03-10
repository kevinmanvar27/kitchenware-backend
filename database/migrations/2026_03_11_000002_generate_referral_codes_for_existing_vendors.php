<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Vendor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate referral codes for existing vendors that don't have one
        $vendors = Vendor::whereNull('referral_code')->get();
        
        foreach ($vendors as $vendor) {
            $vendor->referral_code = Vendor::generateReferralCode($vendor);
            $vendor->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally clear referral codes if rolling back
        // Vendor::query()->update(['referral_code' => null]);
    }
};
