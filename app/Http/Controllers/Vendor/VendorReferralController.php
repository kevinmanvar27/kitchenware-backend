<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\ReferralEarning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorReferralController extends Controller
{
    /**
     * Display vendor's referral code and statistics
     */
    public function myCode()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        // Get total referrals
        $totalReferrals = UserSubscription::where('referral_code', $vendor->referral_code)
            ->whereNotNull('referral_code')
            ->whereIn('status', ['active', 'expired', 'cancelled'])
            ->count();
        
        // Get recent referrals
        $recentReferrals = UserSubscription::where('referral_code', $vendor->referral_code)
            ->whereNotNull('referral_code')
            ->whereIn('status', ['active', 'expired', 'cancelled'])
            ->with('vendor', 'plan')
            ->latest()
            ->take(5)
            ->get();
        
        return view('vendor.referral.my-code', compact('vendor', 'totalReferrals', 'recentReferrals'));
    }

    /**
     * Display vendor's referral earnings
     */
    public function earnings()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        
        // Get all earnings with details
        $earnings = ReferralEarning::where('referrer_vendor_id', $vendor->id)
            ->with(['referredVendor', 'subscription.plan', 'payout'])
            ->latest()
            ->paginate(20);
        
        // Calculate statistics
        $stats = [
            'total_pending' => $vendor->pending_referral_earnings,
            'total_approved' => $vendor->approved_referral_earnings,
            'total_paid' => $vendor->paid_referral_earnings,
            'total_all_time' => $vendor->total_referral_earnings + $vendor->pending_referral_earnings,
            'count_pending' => $vendor->referralEarnings()->pending()->count(),
            'count_approved' => $vendor->referralEarnings()->approved()->count(),
            'count_paid' => $vendor->referralEarnings()->paid()->count(),
        ];
        
        return view('vendor.referral.earnings', compact('vendor', 'earnings', 'stats'));
    }
}

