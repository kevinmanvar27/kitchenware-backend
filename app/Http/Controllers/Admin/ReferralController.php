<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralUser;
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
        
        $query = Referral::withCount([
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
        
        // Calculate pending amount for each referral
        foreach ($referrals as $referral) {
            $referral->calculated_pending_amount = $referral->pending_users_count * $referral->amount;
        }
        
        // Get statistics
        $totalReferrals = Referral::count();
        $totalReferredUsers = ReferralUser::count();
        $totalPaidUsers = ReferralUser::paid()->count();
        $totalPendingUsers = ReferralUser::pending()->count();
        $totalPaidAmount = ReferralUser::paid()->sum('payment_amount');
        
        // Calculate total pending amount (pending users * their referral amount)
        $totalPendingAmount = DB::table('referral_users')
            ->join('referrals', 'referral_users.referral_id', '=', 'referrals.id')
            ->where('referral_users.payment_status', 'pending')
            ->sum('referrals.amount');
        
        $stats = [
            'total' => $totalReferrals,
            'active' => Referral::active()->count(),
            'inactive' => Referral::inactive()->count(),
            'total_referred_users' => $totalReferredUsers,
            'total_amount' => $totalPaidAmount + $totalPendingAmount,
            'total_paid_amount' => $totalPaidAmount,
            'total_pending_amount' => $totalPendingAmount,
            'pending_payments' => $totalPendingUsers,
            'paid_payments' => $totalPaidUsers,
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
        
        $referralUsers = $referral->referralUsers()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate payment statistics for this referral
        $paymentStats = [
            'total_users' => $referral->referralUsers()->count(),
            'pending_users' => $referral->referralUsers()->pending()->count(),
            'paid_users' => $referral->referralUsers()->paid()->count(),
            'total_paid' => $referral->referralUsers()->paid()->sum('payment_amount'),
            'total_pending' => $referral->referralUsers()->pending()->count() * $referral->amount,
            'amount_per_user' => $referral->amount,
        ];
        
        return view('admin.referrals.users', compact('referral', 'referralUsers', 'paymentStats'));
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
