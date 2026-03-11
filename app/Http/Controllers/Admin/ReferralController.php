<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralUser;
use App\Models\ReferralEarning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class ReferralController extends Controller
{
    use LogsActivity;
    
    /**
     * Display a listing of referrals.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Referral::class);
        
        $query = Referral::with('vendor') // Load vendor relationship
            ->withCount([
            'referralUsers',
            'referralUsers as pending_users_count' => function ($q) {
                $q->where('payment_status', 'pending');
            },
            'referralUsers as paid_users_count' => function ($q) {
                $q->where('payment_status', 'paid');
            },
        ])->withSum(['referralUsers as total_paid_amount' => function ($q) {
            $q->where('payment_status', 'paid');
        }], 'payment_amount');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Search by name, phone number, or referral code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }
        
        $referrals = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate pending amount for each referral AND include referral earnings data
        foreach ($referrals as $referral) {
            // Manual referral users (old system)
            $referral->calculated_pending_amount = $referral->pending_users_count * $referral->amount;
            
            // Add vendor referral earnings data (new system)
            if ($referral->vendor) {
                // Count referred vendors who used this code
                $referralEarnings = ReferralEarning::where('referral_code', $referral->referral_code)->get();
                
                $referral->vendor_referred_count = $referralEarnings->count();
                $referral->vendor_pending_count = $referralEarnings->where('status', 'pending')->count();
                $referral->vendor_approved_count = $referralEarnings->where('status', 'approved')->count();
                $referral->vendor_paid_count = $referralEarnings->where('status', 'paid')->count();
                
                $referral->vendor_pending_amount = $referralEarnings->where('status', 'pending')->sum('commission_amount');
                $referral->vendor_approved_amount = $referralEarnings->where('status', 'approved')->sum('commission_amount');
                $referral->vendor_paid_amount = $referralEarnings->where('status', 'paid')->sum('commission_amount');
                
                // Combine with manual referral data
                $referral->total_referred_users = $referral->referral_users_count + $referral->vendor_referred_count;
                $referral->total_pending_users = $referral->pending_users_count + $referral->vendor_pending_count;
                $referral->total_paid_users = $referral->paid_users_count + $referral->vendor_paid_count + $referral->vendor_approved_count;
                $referral->total_paid_amount_combined = ($referral->total_paid_amount ?? 0) + $referral->vendor_paid_amount + $referral->vendor_approved_amount;
                $referral->total_pending_amount_combined = $referral->calculated_pending_amount + $referral->vendor_pending_amount;
            } else {
                // No vendor, use only manual referral data
                $referral->total_referred_users = $referral->referral_users_count;
                $referral->total_pending_users = $referral->pending_users_count;
                $referral->total_paid_users = $referral->paid_users_count;
                $referral->total_paid_amount_combined = $referral->total_paid_amount ?? 0;
                $referral->total_pending_amount_combined = $referral->calculated_pending_amount;
            }
        }
        
        // Get statistics - combine both systems
        $totalReferrals = Referral::count();
        
        // Manual referral users
        $totalReferredUsers = ReferralUser::count();
        $totalPaidUsers = ReferralUser::paid()->count();
        $totalPendingUsers = ReferralUser::pending()->count();
        $totalPaidAmount = ReferralUser::paid()->sum('payment_amount');
        $totalPendingAmount = DB::table('referral_users')
            ->join('referrals', 'referral_users.referral_id', '=', 'referrals.id')
            ->where('referral_users.payment_status', 'pending')
            ->sum('referrals.amount');
        
        // Vendor referral earnings
        $vendorReferredCount = ReferralEarning::count();
        $vendorPaidCount = ReferralEarning::whereIn('status', ['paid', 'approved'])->count();
        $vendorPendingCount = ReferralEarning::where('status', 'pending')->count();
        $vendorPaidAmount = ReferralEarning::whereIn('status', ['paid', 'approved'])->sum('commission_amount');
        $vendorPendingAmount = ReferralEarning::where('status', 'pending')->sum('commission_amount');
        
        $stats = [
            'total' => $totalReferrals,
            'active' => Referral::active()->count(),
            'inactive' => Referral::inactive()->count(),
            'total_referred_users' => $totalReferredUsers + $vendorReferredCount,
            'total_amount' => $totalPaidAmount + $totalPendingAmount + $vendorPaidAmount + $vendorPendingAmount,
            'total_paid_amount' => $totalPaidAmount + $vendorPaidAmount,
            'total_pending_amount' => $totalPendingAmount + $vendorPendingAmount,
            'pending_payments' => $totalPendingUsers + $vendorPendingCount,
            'paid_payments' => $totalPaidUsers + $vendorPaidCount,
        ];
        
        return view('admin.referrals.index', compact('referrals', 'stats'));
    }

    /**
     * Show the form for creating a new referral.
     */
    public function create()
    {
        $this->authorize('create', Referral::class);
        
        return view('admin.referrals.create');
    }

    /**
     * Store a newly created referral.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Referral::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'amount' => 'nullable|numeric|min:0',
            'referral_code' => 'nullable|string|max:20|unique:referrals,referral_code',
            'status' => 'required|in:active,inactive',
            'payment_status' => 'required|in:pending,paid,cancelled',
        ]);
        
        try {
            // Generate or use custom referral code
            $validated['referral_code'] = Referral::generateReferralCode($request->referral_code);
            
            // Set default amount if not provided
            $validated['amount'] = $validated['amount'] ?? 0;
            
            $referral = Referral::create($validated);
            
            // Log activity
            $this->logAdminActivity('created', "Created referral: {$referral->name} (Code: {$referral->referral_code})", $referral);
            
            return redirect()->route('admin.referrals.index')
                ->with('success', 'Referral code created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified referral.
     */
    public function edit(Referral $referral)
    {
        $this->authorize('update', $referral);
        
        return view('admin.referrals.edit', compact('referral'));
    }

    /**
     * Update the specified referral.
     */
    public function update(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'payment_status' => 'required|in:pending,paid,cancelled',
        ]);
        
        // Set default amount if not provided
        $validated['amount'] = $validated['amount'] ?? 0;
        
        $referral->update($validated);
        
        // Log activity
        $this->logAdminActivity('updated', "Updated referral: {$referral->name} (Code: {$referral->referral_code})", $referral);
        
        return redirect()->route('admin.referrals.index')
            ->with('success', 'Referral updated successfully.');
    }

    /**
     * Remove the specified referral.
     */
    public function destroy(Referral $referral)
    {
        $this->authorize('delete', $referral);
        
        // Capture data before deletion for logging
        $referralName = $referral->name;
        $referralCode = $referral->referral_code;
        $referralId = $referral->id;
        
        $referral->delete();
        
        // Log activity
        $this->logAdminActivity('deleted', "Deleted referral: {$referralName} (Code: {$referralCode}, ID: {$referralId})");
        
        return redirect()->route('admin.referrals.index')
            ->with('success', 'Referral deleted successfully.');
    }

    /**
     * Update referral status (AJAX).
     */
    public function updateStatus(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        
        $oldStatus = $referral->status;
        
        $referral->update([
            'status' => $validated['status'],
        ]);
        
        // Log activity
        $this->logAdminActivity('status_changed', "Changed referral status: {$referral->name} ({$oldStatus} → {$referral->status})", $referral);
        
        return response()->json([
            'success' => true,
            'message' => 'Referral status updated successfully.',
            'status' => $referral->status,
        ]);
    }

    /**
     * Update referral payment status (AJAX).
     */
    public function updatePaymentStatus(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,cancelled',
        ]);
        
        $oldStatus = $referral->payment_status;
        
        $referral->update([
            'payment_status' => $validated['payment_status'],
        ]);
        
        // Log activity
        $this->logAdminActivity('payment_status_changed', "Changed referral payment status: {$referral->name} ({$oldStatus} → {$referral->payment_status})", $referral);
        
        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully.',
            'payment_status' => $referral->payment_status,
        ]);
    }

    /**
     * Display referral users for a specific referral.
     */
    public function users(Referral $referral)
    {
        $this->authorize('view', $referral);
        
        // Get manual referral users (old system)
        $referralUsers = $referral->referralUsers()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get vendor referral earnings (new system) - vendors who used this referral code
        $referralEarnings = ReferralEarning::where('referral_code', $referral->referral_code)
            ->with(['referredVendor', 'referrerVendor'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate payment statistics combining both systems
        $manualTotalUsers = $referral->referralUsers()->count();
        $manualPendingUsers = $referral->referralUsers()->pending()->count();
        $manualPaidUsers = $referral->referralUsers()->paid()->count();
        $manualTotalPaid = $referral->referralUsers()->paid()->sum('payment_amount');
        $manualTotalPending = $referral->referralUsers()->pending()->count() * $referral->amount;
        
        // Vendor earnings stats
        $vendorTotalUsers = $referralEarnings->count();
        $vendorPendingUsers = $referralEarnings->where('status', 'pending')->count();
        $vendorPaidUsers = $referralEarnings->whereIn('status', ['paid', 'approved'])->count();
        $vendorTotalPaid = $referralEarnings->whereIn('status', ['paid', 'approved'])->sum('commission_amount');
        $vendorTotalPending = $referralEarnings->where('status', 'pending')->sum('commission_amount');
        
        $paymentStats = [
            'total_users' => $manualTotalUsers + $vendorTotalUsers,
            'pending_users' => $manualPendingUsers + $vendorPendingUsers,
            'paid_users' => $manualPaidUsers + $vendorPaidUsers,
            'total_paid' => $manualTotalPaid + $vendorTotalPaid,
            'total_pending' => $manualTotalPending + $vendorTotalPending,
            'amount_per_user' => $referral->amount,
            // Separate counts for display
            'manual_users' => $manualTotalUsers,
            'vendor_users' => $vendorTotalUsers,
        ];
        
        return view('admin.referrals.users', compact('referral', 'referralUsers', 'referralEarnings', 'paymentStats'));
    }

    /**
     * Store a new referral user manually.
     */
    public function storeUser(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $validated['referral_id'] = $referral->id;
        $validated['payment_status'] = 'pending';
        $validated['payment_amount'] = 0;
        
        $referralUser = ReferralUser::create($validated);
        
        // Log activity
        $this->logAdminActivity('created', "Added referral user: {$referralUser->name} to referral {$referral->name}", $referralUser);
        
        return redirect()->route('admin.referrals.users', $referral)
            ->with('success', 'Referral user added successfully.');
    }

    /**
     * Remove a referral user.
     */
    public function destroyUser(Referral $referral, ReferralUser $referralUser)
    {
        $this->authorize('update', $referral);
        
        // Ensure the referral user belongs to this referral
        if ($referralUser->referral_id !== $referral->id) {
            return redirect()->route('admin.referrals.users', $referral)
                ->with('error', 'Invalid referral user.');
        }
        
        // Don't allow deletion of paid users
        if ($referralUser->isPaid()) {
            return redirect()->route('admin.referrals.users', $referral)
                ->with('error', 'Cannot delete a user with completed payment.');
        }
        
        // Capture data before deletion for logging
        $userName = $referralUser->name;
        $userId = $referralUser->id;
        
        $referralUser->delete();
        
        // Log activity
        $this->logAdminActivity('deleted', "Removed referral user: {$userName} (ID: {$userId}) from referral {$referral->name}");
        
        return redirect()->route('admin.referrals.users', $referral)
            ->with('success', 'Referral user removed successfully.');
    }

    /**
     * Mark a referral user's payment as paid (AJAX).
     */
    public function markUserPaid(Request $request, Referral $referral, ReferralUser $referralUser)
    {
        $this->authorize('update', $referral);
        
        // Ensure the referral user belongs to this referral
        if ($referralUser->referral_id !== $referral->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral user.',
            ], 400);
        }
        
        // Don't allow changing already paid users
        if ($referralUser->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'This user has already been marked as paid.',
            ], 400);
        }
        
        $validated = $request->validate([
            'payment_notes' => 'nullable|string|max:255',
        ]);
        
        $referralUser->update([
            'payment_status' => 'paid',
            'payment_amount' => $referral->amount,
            'paid_at' => now(),
            'payment_notes' => $validated['payment_notes'] ?? null,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payment marked as completed.',
            'payment_amount' => $referral->amount,
            'paid_at' => $referralUser->paid_at->format('d M Y, h:i A'),
        ]);
    }

    /**
     * Mark multiple referral users' payments as paid.
     */
    public function markMultiplePaid(Request $request, Referral $referral)
    {
        $this->authorize('update', $referral);
        
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:referral_users,id',
            'payment_notes' => 'nullable|string|max:255',
        ]);
        
        $count = 0;
        foreach ($validated['user_ids'] as $userId) {
            $referralUser = ReferralUser::find($userId);
            
            // Skip if not belonging to this referral or already paid
            if (!$referralUser || $referralUser->referral_id !== $referral->id || $referralUser->isPaid()) {
                continue;
            }
            
            $referralUser->update([
                'payment_status' => 'paid',
                'payment_amount' => $referral->amount,
                'paid_at' => now(),
                'payment_notes' => $validated['payment_notes'] ?? null,
            ]);
            
            $count++;
        }
        
        return redirect()->route('admin.referrals.users', $referral)
            ->with('success', "{$count} payment(s) marked as completed.");
    }
}
