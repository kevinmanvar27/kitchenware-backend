<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralEarning;
use App\Models\VendorPayout;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralEarningController extends Controller
{
    /**
     * Display list of referral earnings
     */
    public function index(Request $request)
    {
        $query = ReferralEarning::with([
            'referrerVendor',
            'referredVendor',
            'subscription.plan',
            'payout'
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by referrer vendor
        if ($request->filled('referrer_vendor_id')) {
            $query->where('referrer_vendor_id', $request->referrer_vendor_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referral_code', 'like', "%{$search}%")
                  ->orWhereHas('referrerVendor', function($q) use ($search) {
                      $q->where('store_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('referredVendor', function($q) use ($search) {
                      $q->where('store_name', 'like', "%{$search}%");
                  });
            });
        }

        $earnings = $query->latest()->paginate(20);

        // Summary statistics
        $stats = [
            'total_pending' => ReferralEarning::pending()->sum('commission_amount'),
            'total_approved' => ReferralEarning::approved()->sum('commission_amount'),
            'total_paid' => ReferralEarning::paid()->sum('commission_amount'),
            'count_pending' => ReferralEarning::pending()->count(),
            'count_approved' => ReferralEarning::approved()->count(),
            'count_paid' => ReferralEarning::paid()->count(),
        ];

        // Get vendors with referral earnings for filter
        $vendors = Vendor::whereHas('referralEarnings')->get();

        return view('admin.referral-earnings.index', compact('earnings', 'stats', 'vendors'));
    }

    /**
     * Show details of a specific earning
     */
    public function show(ReferralEarning $earning)
    {
        $earning->load([
            'referrerVendor',
            'referredVendor',
            'subscription.plan',
            'subscription.user',
            'payout'
        ]);

        return view('admin.referral-earnings.show', compact('earning'));
    }

    /**
     * Approve a referral earning
     */
    public function approve(Request $request, ReferralEarning $earning)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if (!$earning->canBeApproved()) {
            return back()->with('error', 'This earning cannot be approved. Subscription must be active.');
        }

        $earning->approve($request->admin_notes);

        return back()->with('success', 'Referral earning approved successfully!');
    }

    /**
     * Bulk approve earnings
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'earning_ids' => 'required|array',
            'earning_ids.*' => 'exists:referral_earnings,id',
        ]);

        $earningIds = json_decode($request->earning_ids);
        
        $approved = 0;
        $failed = 0;
        
        foreach ($earningIds as $id) {
            $earning = ReferralEarning::find($id);
            if ($earning && $earning->approve()) {
                $approved++;
            } else {
                $failed++;
            }
        }

        $message = "{$approved} referral earning(s) approved successfully!";
        if ($failed > 0) {
            $message .= " {$failed} earning(s) could not be approved.";
        }

        return back()->with('success', $message);
    }

    /**
     * Create payout for approved earnings
     */
    public function createPayout(Request $request)
    {
        $request->validate([
            'referrer_vendor_id' => 'required|exists:vendors,id',
            'earning_ids' => 'required|array',
            'earning_ids.*' => 'exists:referral_earnings,id',
        ]);

        try {
            DB::beginTransaction();

            // Get approved earnings
            $earnings = ReferralEarning::whereIn('id', $request->earning_ids)
                ->where('referrer_vendor_id', $request->referrer_vendor_id)
                ->where('status', 'approved')
                ->whereNull('payout_id')
                ->get();

            if ($earnings->isEmpty()) {
                return back()->with('error', 'No approved earnings found for payout.');
            }

            $totalAmount = $earnings->sum('commission_amount');

            // Create payout record
            $payout = VendorPayout::create([
                'vendor_id' => $request->referrer_vendor_id,
                'amount' => $totalAmount,
                'status' => 'pending',
                'payout_type' => 'referral_commission',
                'notes' => 'Referral commission payout for ' . $earnings->count() . ' earnings',
                'requested_at' => now(),
            ]);

            // Mark earnings as paid
            foreach ($earnings as $earning) {
                $earning->markAsPaid($payout->id);
            }

            DB::commit();

            Log::info('Referral payout created', [
                'payout_id' => $payout->id,
                'vendor_id' => $request->referrer_vendor_id,
                'amount' => $totalAmount,
                'earnings_count' => $earnings->count(),
            ]);

            return redirect()
                ->route('admin.payouts.show', $payout->id)
                ->with('success', 'Payout created successfully! Total amount: ₹' . number_format($totalAmount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create referral payout', [
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to create payout. Please try again.');
        }
    }

    /**
     * Get earnings by vendor (for payout creation)
     */
    public function byVendor(Request $request, Vendor $vendor)
    {
        $earnings = ReferralEarning::with(['referredVendor', 'subscription.plan'])
            ->where('referrer_vendor_id', $vendor->id)
            ->approved()
            ->whereNull('payout_id')
            ->get();

        $totalAmount = $earnings->sum('commission_amount');

        return response()->json([
            'success' => true,
            'earnings' => $earnings,
            'total_amount' => $totalAmount,
            'count' => $earnings->count(),
        ]);
    }
}
