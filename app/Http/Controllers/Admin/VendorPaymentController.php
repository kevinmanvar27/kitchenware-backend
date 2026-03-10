<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorWallet;
use App\Models\VendorBankAccount;
use App\Models\VendorPayout;
use App\Models\VendorEarning;
use App\Models\PayoutLog;
use App\Models\PayoutSchedule;
use App\Services\RazorpayXService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PayoutProcessed;
use App\Jobs\ProcessBulkPayouts;
use App\Traits\LogsActivity;

class VendorPaymentController extends Controller
{
    use LogsActivity;
    
    protected RazorpayXService $razorpayService;

    public function __construct(RazorpayXService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Display vendor payment summary page
     */
    public function index(Request $request)
    {
        $query = Vendor::with(['user', 'wallet', 'primaryBankAccount', 'payouts' => function($q) {
            $q->latest()->limit(1);
        }])
        ->approved();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('wallet_status') && $request->wallet_status !== 'all') {
            $query->whereHas('wallet', function($q) use ($request) {
                $q->where('status', $request->wallet_status);
            });
        }

        // Bank status filter
        if ($request->has('bank_status')) {
            if ($request->bank_status === 'created') {
                $query->whereHas('primaryBankAccount', function($q) {
                    $q->where('fund_account_status', 'created');
                });
            } elseif ($request->bank_status === 'pending') {
                $query->where(function($q) {
                    $q->whereDoesntHave('primaryBankAccount')
                      ->orWhereHas('primaryBankAccount', function($bq) {
                          $bq->where('fund_account_status', '!=', 'created');
                      });
                });
            }
        }

        $vendors = $query->latest()->get();

        // Get summary statistics
        $stats = [
            'total_vendors' => Vendor::approved()->count(),
            'total_pending' => VendorWallet::where('pending_amount', '>', 0)->sum('pending_amount'),
            'total_paid_this_month' => VendorPayout::completed()
                ->whereMonth('processed_at', now()->month)
                ->whereYear('processed_at', now()->year)
                ->sum('amount'),
            'pending_payouts' => VendorPayout::pending()->count(),
        ];

        // Get RazorpayX balance
        $balance = $this->razorpayService->getBalance();
        $stats['razorpayx_balance'] = $balance['success'] ? ($balance['data']['balance_rupees'] ?? 0) : 0;

        // Get existing payout schedule settings
        $payoutSchedule = PayoutSchedule::first();

        return view('admin.vendor-payments.index', compact('vendors', 'stats', 'payoutSchedule'));
    }

    /**
     * Show vendor payment details
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['user', 'wallet', 'bankAccounts', 'primaryBankAccount']);

        // Ensure wallet exists
        $wallet = $vendor->getOrCreateWallet();
        
        // Get primary bank account
        $bankAccount = $vendor->primaryBankAccount;

        // Get earnings with pagination
        $earnings = $vendor->earnings()
            ->with(['invoice'])
            ->latest()
            ->paginate(20, ['*'], 'earnings_page');

        // Get payouts with pagination
        $payouts = $vendor->payouts()
            ->with(['logs', 'processedByUser'])
            ->latest()
            ->paginate(20, ['*'], 'payouts_page');

        // Get recent payout logs
        $payoutLogs = PayoutLog::whereIn('payout_id', $vendor->payouts()->pluck('id'))
            ->with('payout')
            ->latest()
            ->limit(20)
            ->get();

        // Get earnings summary
        $earningsSummary = [
            'total' => $vendor->earnings()->sum('vendor_earning'),
            'pending' => $vendor->earnings()->where('status', 'pending')->sum('vendor_earning'),
            'confirmed' => $vendor->earnings()->where('status', 'confirmed')->sum('vendor_earning'),
            'paid' => $vendor->earnings()->where('status', 'paid')->sum('vendor_earning'),
        ];

        // Get payout summary
        $payoutSummary = [
            'total' => $vendor->payouts()->sum('amount'),
            'completed' => $vendor->payouts()->completed()->sum('amount'),
            'pending' => $vendor->payouts()->pending()->sum('amount'),
            'failed' => $vendor->payouts()->failed()->count(),
        ];

        return view('admin.vendor-payments.show', compact(
            'vendor', 
            'wallet', 
            'bankAccount',
            'earnings',
            'payouts',
            'payoutLogs',
            'earningsSummary', 
            'payoutSummary'
        ));
    }

    /**
     * Initiate manual payout for vendor
     */
    public function initiatePayout(Request $request, Vendor $vendor)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payout_mode' => 'required|in:NEFT,RTGS,IMPS,UPI',
            'notes' => 'nullable|string|max:500',
        ]);

        $wallet = $vendor->wallet;
        
        if (!$wallet || $wallet->status !== VendorWallet::STATUS_ACTIVE) {
            return back()->with('error', 'Vendor wallet is not active or does not exist.');
        }

        if ($request->amount > $wallet->payable_amount) {
            return back()->with('error', 'Payout amount exceeds available balance. Available: ₹' . number_format($wallet->payable_amount, 2));
        }

        $bankAccount = $vendor->primaryBankAccount;
        if (!$bankAccount) {
            return back()->with('error', 'Vendor does not have a bank account configured.');
        }

        DB::beginTransaction();
        try {
            // Create payout record
            $payout = VendorPayout::create([
                'vendor_id' => $vendor->id,
                'amount' => $request->amount,
                'status' => VendorPayout::STATUS_PENDING,
                'payment_method' => 'razorpayx',
                'payout_mode' => $request->payout_mode,
                'notes' => $request->notes,
                'requested_at' => now(),
                'processed_by' => Auth::id(),
                'is_automated' => false,
                'bank_details' => [
                    'account_number' => $bankAccount->masked_account_number,
                    'ifsc_code' => $bankAccount->ifsc_code,
                    'bank_name' => $bankAccount->bank_name,
                    'account_holder' => $bankAccount->account_holder_name,
                ],
            ]);

            // Process payout via RazorpayX
            $result = $this->razorpayService->processPayout($payout, $request->payout_mode);

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', 'Payout failed: ' . $result['error']);
            }

            // Update wallet after successful payout initiation
            $payout->refresh();
            $wallet->total_paid += $request->amount;
            $wallet->pending_amount -= $request->amount;
            $wallet->last_payout_at = now();
            $wallet->save();

            DB::commit();

            // Send notification to vendor if they have a user account
            if ($vendor->user) {
                try {
                    Notification::send($vendor->user, new PayoutProcessed($payout));
                } catch (\Exception $e) {
                    Log::error('Failed to send payout notification', [
                        'vendor_id' => $vendor->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Log activity
            $this->logAdminActivity('initiated_payout', "Initiated payout of ₹{$request->amount} for vendor: {$vendor->store_name} via {$request->payout_mode}", $payout);
            
            return back()->with('success', 'Payout initiated successfully. Razorpay Payout ID: ' . ($payout->razorpay_payout_id ?? 'Processing'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payout initiation failed', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Payout failed: ' . $e->getMessage());
        }
    }

    /**
     * Process bulk payouts for multiple vendors
     */
    public function processBulkPayout(Request $request)
    {
        $request->validate([
            'vendors' => 'required|array|min:1',
            'vendors.*' => 'required|exists:vendors,id',
            'payout_mode' => 'required|in:NEFT,RTGS,IMPS,UPI',
            'notes' => 'nullable|string|max:500',
            'pay_full_amount' => 'nullable|boolean',
        ]);

        $vendorIds = $request->vendors;
        $payFullAmount = $request->boolean('pay_full_amount', true);
        $payoutMode = $request->payout_mode;
        $notes = $request->notes;

        // Get RazorpayX balance
        $balanceResult = $this->razorpayService->getBalance();
        if (!$balanceResult['success']) {
            return back()->with('error', 'Failed to fetch RazorpayX balance: ' . $balanceResult['error']);
        }

        $availableBalance = $balanceResult['data']['balance_rupees'] ?? 0;

        // Get eligible vendors with active wallets and bank accounts
        $vendors = Vendor::whereIn('id', $vendorIds)
            ->with(['wallet' => function($q) {
                $q->where('status', VendorWallet::STATUS_ACTIVE)
                  ->where('pending_amount', '>', 0);
            }, 'primaryBankAccount' => function($q) {
                $q->where('fund_account_status', 'created');
            }])
            ->get()
            ->filter(function($vendor) {
                return $vendor->wallet && $vendor->primaryBankAccount;
            });

        if ($vendors->isEmpty()) {
            return back()->with('error', 'No eligible vendors found for bulk payout.');
        }

        // Calculate total payout amount
        $totalAmount = $vendors->sum(function($vendor) {
            return $vendor->wallet->payable_amount;
        });

        if ($totalAmount <= 0) {
            return back()->with('error', 'No funds available for payout.');
        }

        if ($totalAmount > $availableBalance) {
            return back()->with('error', "Insufficient RazorpayX balance. Required: ₹{$totalAmount}, Available: ₹{$availableBalance}");
        }

        // Process payouts in the background
        ProcessBulkPayouts::dispatch($vendors->pluck('id')->toArray(), $payoutMode, $notes, $payFullAmount, Auth::id());
        
        // Log activity
        $this->logAdminActivity('initiated_bulk_payout', "Initiated bulk payout for {$vendors->count()} vendors totaling ₹{$totalAmount} via {$payoutMode}");

        return back()->with('success', 'Bulk payout initiated for ' . $vendors->count() . ' vendors. Processing in the background.');
    }

    /**
     * Save or update payout schedule settings
     */
    public function savePayoutSchedule(Request $request)
    {
        $request->validate([
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
            ],
            'payout_mode' => 'required|in:NEFT,RTGS,IMPS,UPI',
            'enabled' => 'nullable|boolean',
        ], [
            'scheduled_at.after' => 'The scheduled date and time must be in the future.',
            'scheduled_at.required' => 'Please select a date and time for the payout.',
            'scheduled_at.date' => 'Please provide a valid date and time.',
        ]);

        // Additional validation: Ensure the date is not in the past (allow dates even 1 second in future)
        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
        if ($scheduledAt->isPast()) {
            return back()->withErrors([
                'scheduled_at' => 'The scheduled date and time must be in the future. Past dates are not allowed.'
            ])->withInput();
        }

        // Get or create schedule settings
        $schedule = PayoutSchedule::first();
        if (!$schedule) {
            $schedule = new PayoutSchedule();
        }

        // Update settings with calendar-based scheduling
        $schedule->scheduled_at = $scheduledAt;
        $schedule->payout_mode = $request->payout_mode;
        $schedule->enabled = $request->boolean('enabled', false);
        $schedule->updated_by = Auth::id();
        
        // Clear old frequency-based fields (optional, for clean data)
        $schedule->frequency = null;
        $schedule->day = null;
        $schedule->payout_time = null;
        
        $schedule->save();

        $formattedDate = $scheduledAt->format('M d, Y h:i A');
        $message = $schedule->enabled 
            ? "Payout schedule activated! Automatic payout will be processed on {$formattedDate}." 
            : "Payout schedule saved for {$formattedDate}. Enable it to activate automatic processing.";

        return back()->with('success', $message);
    }

    /**
     * Setup bank account for RazorpayX
     */
    public function setupBankAccount(Request $request, Vendor $vendor)
    {
        $bankAccount = $vendor->primaryBankAccount;
        
        if (!$bankAccount) {
            return back()->with('error', 'Vendor does not have a bank account configured.');
        }

        if ($bankAccount->hasFundAccount()) {
            return back()->with('info', 'Bank account is already setup for payouts.');
        }

        $result = $this->razorpayService->setupVendorForPayouts($vendor, $bankAccount);

        if (!$result['success']) {
            return back()->with('error', 'Failed to setup bank account: ' . $result['error']);
        }

        return back()->with('success', 'Bank account setup successfully for RazorpayX payouts.');
    }

    /**
     * Update wallet status (hold/active)
     */
    public function updateWalletStatus(Request $request, Vendor $vendor)
    {
        $request->validate([
            'status' => 'required|in:active,hold,suspended',
        ]);

        $wallet = $vendor->getOrCreateWallet();
        $oldStatus = $wallet->status;
        $wallet->update(['status' => $request->status]);
        
        // Log activity
        $this->logAdminActivity('wallet_status_changed', "Changed wallet status for vendor {$vendor->store_name}: {$oldStatus} → {$request->status}", $wallet);

        return back()->with('success', 'Wallet status updated to ' . ucfirst($request->status));
    }

    /**
     * Add/Update bank account
     */
    public function updateBankAccount(Request $request, Vendor $vendor)
    {
        $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:20',
            'ifsc_code' => 'required|string|size:11',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'account_type' => 'required|in:savings,current',
        ]);

        $bankAccount = $vendor->primaryBankAccount;

        if ($bankAccount) {
            // Prepare update data
            $updateData = [
                'account_holder_name' => $request->account_holder_name,
                'ifsc_code' => $request->ifsc_code,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'account_type' => $request->account_type
            ];
            
            // Only update account number if provided
            if (!empty($request->account_number)) {
                $updateData['account_number'] = $request->account_number;
                
                // If account number or IFSC changed, reset RazorpayX details
                $resetRazorpay = $bankAccount->ifsc_code !== $request->ifsc_code;
                
                if ($resetRazorpay) {
                    $updateData['razorpay_contact_id'] = null;
                    $updateData['razorpay_fund_account_id'] = null;
                    $updateData['fund_account_status'] = VendorBankAccount::FUND_STATUS_PENDING;
                    $updateData['fund_account_error'] = null;
                }
            }

            $bankAccount->update($updateData);
            
            // Log activity
            $this->logAdminActivity('updated', "Updated bank account for vendor: {$vendor->store_name}", $bankAccount);
        } else {
            // Require account number for new accounts
            if (empty($request->account_number)) {
                return back()->with('error', 'Account number is required when adding a new bank account.');
            }
            
            $newBankAccount = VendorBankAccount::create([
                'vendor_id' => $vendor->id,
                'account_holder_name' => $request->account_holder_name,
                'account_number' => $request->account_number,
                'ifsc_code' => $request->ifsc_code,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'account_type' => $request->account_type,
                'is_primary' => true,
            ]);
            
            // Log activity
            $this->logAdminActivity('created', "Added bank account for vendor: {$vendor->store_name}", $newBankAccount);
        }

        return back()->with('success', 'Bank account updated successfully.');
    }

    /**
     * Retry failed payout
     */
    public function retryPayout(VendorPayout $payout)
    {
        if (!$payout->canRetry()) {
            return back()->with('error', 'This payout cannot be retried. Maximum retry attempts reached or payout is not in failed status.');
        }

        $payout->update(['status' => VendorPayout::STATUS_PENDING]);
        
        $result = $this->razorpayService->processPayout($payout, $payout->payout_mode);

        if (!$result['success']) {
            return back()->with('error', 'Retry failed: ' . $result['error']);
        }

        return back()->with('success', 'Payout retry initiated successfully.');
    }

    /**
     * Get payout details (AJAX)
     */
    public function getPayoutDetails(VendorPayout $payout)
    {
        try {
            $payout->load(['vendor', 'logs', 'processedByUser']);
            
            return response()->json([
                'success' => true,
                'payout' => $payout,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading payout details', [
                'payout_id' => $payout->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load payout details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync payout status from RazorpayX
     */
    public function syncPayoutStatus(VendorPayout $payout)
    {
        if (!$payout->razorpay_payout_id) {
            return back()->with('error', 'Payout does not have a RazorpayX ID.');
        }

        $result = $this->razorpayService->getPayoutStatus($payout->razorpay_payout_id);

        if (!$result['success']) {
            return back()->with('error', 'Failed to fetch status: ' . $result['error']);
        }

        $razorpayData = $result['data'];
        $oldStatus = $payout->status;
        $newStatus = $this->mapRazorpayStatus($razorpayData['status']);

        $payout->update([
            'status' => $newStatus,
            'razorpay_status' => $razorpayData['status'],
            'utr' => $razorpayData['utr'] ?? $payout->utr,
            'failure_reason' => $razorpayData['failure_reason'] ?? $payout->failure_reason,
            'processed_at' => in_array($newStatus, [VendorPayout::STATUS_COMPLETED, VendorPayout::STATUS_FAILED]) 
                ? now() 
                : $payout->processed_at,
        ]);

        if ($oldStatus !== $newStatus) {
            $payout->addLog(PayoutLog::EVENT_PROCESSING, $razorpayData['status'], $razorpayData, 'Status synced from RazorpayX');

            // Update wallet if completed
            if ($newStatus === VendorPayout::STATUS_COMPLETED && $oldStatus !== VendorPayout::STATUS_COMPLETED) {
                $wallet = $payout->vendor->wallet;
                if ($wallet) {
                    $wallet->recordPayout($payout->amount);
                }
                
                // Send notification to vendor if they have a user account
                if ($payout->vendor->user) {
                    try {
                        Notification::send($payout->vendor->user, new PayoutProcessed($payout));
                    } catch (\Exception $e) {
                        Log::error('Failed to send payout notification', [
                            'payout_id' => $payout->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'Payout status synced. Current status: ' . ucfirst($razorpayData['status']));
    }

    /**
     * Sync all pending payouts
     */
    public function syncAllPendingPayouts()
    {
        $pendingPayouts = VendorPayout::whereIn('status', [VendorPayout::STATUS_PENDING, VendorPayout::STATUS_PROCESSING])
            ->whereNotNull('razorpay_payout_id')
            ->get();
            
        if ($pendingPayouts->isEmpty()) {
            return back()->with('info', 'No pending payouts to sync.');
        }
        
        $syncCount = 0;
        $errorCount = 0;
        
        foreach ($pendingPayouts as $payout) {
            $result = $this->razorpayService->getPayoutStatus($payout->razorpay_payout_id);
            
            if ($result['success']) {
                $razorpayData = $result['data'];
                $oldStatus = $payout->status;
                $newStatus = $this->mapRazorpayStatus($razorpayData['status']);
                
                $payout->update([
                    'status' => $newStatus,
                    'razorpay_status' => $razorpayData['status'],
                    'utr' => $razorpayData['utr'] ?? $payout->utr,
                    'failure_reason' => $razorpayData['failure_reason'] ?? $payout->failure_reason,
                    'processed_at' => in_array($newStatus, [VendorPayout::STATUS_COMPLETED, VendorPayout::STATUS_FAILED]) 
                        ? now() 
                        : $payout->processed_at,
                ]);
                
                if ($oldStatus !== $newStatus) {
                    $payout->addLog(PayoutLog::EVENT_PROCESSING, $razorpayData['status'], $razorpayData, 'Status synced from RazorpayX');
                    
                    // Update wallet if completed
                    if ($newStatus === VendorPayout::STATUS_COMPLETED) {
                        $wallet = $payout->vendor->wallet;
                        if ($wallet) {
                            $wallet->recordPayout($payout->amount);
                        }
                        
                        // Send notification
                        if ($payout->vendor->user) {
                            try {
                                Notification::send($payout->vendor->user, new PayoutProcessed($payout));
                            } catch (\Exception $e) {
                                Log::error('Failed to send payout notification', [
                                    'payout_id' => $payout->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }
                }
                
                $syncCount++;
            } else {
                $errorCount++;
                Log::error('Failed to sync payout status', [
                    'payout_id' => $payout->id,
                    'error' => $result['error'],
                ]);
            }
        }
        
        return back()->with('success', "Synced {$syncCount} payouts successfully. {$errorCount} failed.");
    }

    /**
     * Get RazorpayX balance (AJAX)
     */
    public function getBalance()
    {
        $result = $this->razorpayService->getBalance();

        return response()->json([
            'success' => $result['success'],
            'balance' => $result['success'] ? $result['data']['balance_rupees'] : 0,
            'error' => $result['error'] ?? null,
        ]);
    }

    /**
     * Refresh RazorpayX balance (AJAX)
     */
    public function refreshRazorpayXBalance()
    {
        $result = $this->razorpayService->getBalance();

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch RazorpayX balance: ' . $result['error']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'balance' => $result['data']['balance_rupees'] ?? 0,
            'formatted_balance' => '₹' . number_format($result['data']['balance_rupees'] ?? 0, 2)
        ]);
    }

    /**
     * Map RazorpayX status to internal status
     */
    protected function mapRazorpayStatus(string $razorpayStatus): string
    {
        return match($razorpayStatus) {
            'queued', 'pending' => VendorPayout::STATUS_PENDING,
            'processing' => VendorPayout::STATUS_PROCESSING,
            'processed' => VendorPayout::STATUS_COMPLETED,
            'rejected', 'cancelled' => VendorPayout::STATUS_REJECTED,
            'failed' => VendorPayout::STATUS_FAILED,
            'reversed' => VendorPayout::STATUS_REVERSED,
            default => VendorPayout::STATUS_PROCESSING,
        };
    }

    /**
     * Handle RazorpayX webhook
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        // Verify signature
        if (!$this->razorpayService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('RazorpayX webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        $result = $this->razorpayService->handleWebhook($data);

        return response()->json(['success' => $result]);
    }

    /**
     * Display vendor earnings and commission report
     */
    public function earningsReport(Request $request)
    {
        $query = Vendor::with(['user', 'wallet', 'earnings'])
            ->approved();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        // Get vendors with calculated earnings
        $vendors = $query->get()->map(function($vendor) use ($startDate, $endDate) {
            // Get earnings for the date range
            $earnings = VendorEarning::where('vendor_id', $vendor->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // Calculate totals
            $totalOrderAmount = $earnings->sum('order_amount');
            $totalCommission = $earnings->sum('commission_amount');
            $totalVendorEarning = $earnings->sum('vendor_earning');
            
            // Get lifetime stats from wallet
            $wallet = $vendor->wallet;
            
            return [
                'vendor' => $vendor,
                'period_order_amount' => $totalOrderAmount,
                'period_commission' => $totalCommission,
                'period_vendor_earning' => $totalVendorEarning,
                'lifetime_earned' => $wallet ? $wallet->total_earned : 0,
                'lifetime_paid' => $wallet ? $wallet->total_paid : 0,
                'pending_amount' => $wallet ? $wallet->pending_amount : 0,
                'commission_rate' => $vendor->commission_rate ?? 0,
                'total_orders' => $earnings->count(),
            ];
        });

        // Sort by period commission (descending)
        $vendors = $vendors->sortByDesc('period_commission')->values();

        // Calculate summary statistics
        $stats = [
            'total_vendors' => $vendors->count(),
            'period_total_orders' => $vendors->sum('period_order_amount'),
            'period_total_commission' => $vendors->sum('period_commission'),
            'period_total_vendor_earnings' => $vendors->sum('period_vendor_earning'),
            'lifetime_total_earned' => $vendors->sum('lifetime_earned'),
            'lifetime_total_paid' => $vendors->sum('lifetime_paid'),
            'lifetime_pending' => $vendors->sum('pending_amount'),
        ];

        // Get top performing vendors
        $topVendors = $vendors->take(5);

        // Get commission breakdown by rate
        $commissionBreakdown = Vendor::approved()
            ->selectRaw('commission_rate, COUNT(*) as vendor_count')
            ->groupBy('commission_rate')
            ->orderBy('commission_rate')
            ->get();

        return view('admin.vendor-payments.earnings-report', compact(
            'vendors',
            'stats',
            'topVendors',
            'commissionBreakdown',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export earnings report to CSV
     */
    public function exportEarningsReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $vendors = Vendor::with(['user', 'wallet', 'earnings'])
            ->approved()
            ->get()
            ->map(function($vendor) use ($startDate, $endDate) {
                $earnings = VendorEarning::where('vendor_id', $vendor->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();

                $wallet = $vendor->wallet;

                return [
                    'Vendor Name' => $vendor->store_name,
                    'Owner Name' => $vendor->user->name ?? 'N/A',
                    'Email' => $vendor->business_email ?? $vendor->user->email ?? 'N/A',
                    'Commission Rate (%)' => $vendor->commission_rate ?? 0,
                    'Period Orders' => $earnings->count(),
                    'Period Order Amount' => number_format($earnings->sum('order_amount'), 2),
                    'Period Commission' => number_format($earnings->sum('commission_amount'), 2),
                    'Period Vendor Earning' => number_format($earnings->sum('vendor_earning'), 2),
                    'Lifetime Earned' => number_format($wallet ? $wallet->total_earned : 0, 2),
                    'Lifetime Paid' => number_format($wallet ? $wallet->total_paid : 0, 2),
                    'Pending Amount' => number_format($wallet ? $wallet->pending_amount : 0, 2),
                ];
            });

        $filename = 'vendor-earnings-report-' . $startDate . '-to-' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($vendors) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if ($vendors->isNotEmpty()) {
                fputcsv($file, array_keys($vendors->first()));
            }
            
            // Add data
            foreach ($vendors as $vendor) {
                fputcsv($file, $vendor);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
