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
     * 
     * This migration syncs existing vendor referral codes to the referrals table
     * so they appear in the admin referrals page at /admin/referrals
     */
    public function up(): void
    {
        // Get all vendors with referral codes
        $vendors = Vendor::whereNotNull('referral_code')->get();

        foreach ($vendors as $vendor) {
            // Check if referral entry already exists
            $existingReferral = Referral::where('referral_code', $vendor->referral_code)->first();
            
            if (!$existingReferral) {
                // Create referral entry for this vendor
                try {
                    Referral::create([
                        'name' => $vendor->store_name,
                        'phone_number' => $vendor->business_phone,
                        'amount' => 0, // Default amount, admin can update later
                        'referral_code' => $vendor->referral_code,
                        'status' => 'active',
                        'payment_status' => 'pending',
                    ]);
                } catch (\Exception $e) {
                    // Log error but continue with other vendors
                    \Log::error("Failed to create referral entry for vendor {$vendor->id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all vendors with referral codes
        $vendors = Vendor::whereNotNull('referral_code')->get();

        foreach ($vendors as $vendor) {
            // Delete referral entry for this vendor
            Referral::where('referral_code', $vendor->referral_code)->delete();
        }
    }
};
