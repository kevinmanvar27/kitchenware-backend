<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorPayout;
use App\Models\VendorWallet;
use App\Notifications\PayoutProcessed;
use App\Services\RazorpayXService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBulkPayouts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The vendor IDs to process payouts for.
     *
     * @var array
     */
    protected $vendorIds;

    /**
     * The payout mode to use.
     *
     * @var string
     */
    protected $payoutMode;

    /**
     * Notes for the payout.
     *
     * @var string|null
     */
    protected $notes;

    /**
     * Whether to pay the full amount or not.
     *
     * @var bool
     */
    protected $payFullAmount;

    /**
     * The ID of the user who initiated the payout.
     *
     * @var int
     */
    protected $userId;

    /**
     * The results of the bulk payout operation.
     *
     * @var array
     */
    protected $results = [
        'success' => 0,
        'failed' => 0,
        'total' => 0,
        'amount' => 0,
        'details' => []
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(array $vendorIds, string $payoutMode, ?string $notes, bool $payFullAmount, int $userId)
    {
        $this->vendorIds = $vendorIds;
        $this->payoutMode = $payoutMode;
        $this->notes = $notes;
        $this->payFullAmount = $payFullAmount;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(RazorpayXService $razorpayXService): void
    {
        Log::info('Starting bulk payout process for ' . count($this->vendorIds) . ' vendors');
        $this->results['total'] = count($this->vendorIds);

        foreach ($this->vendorIds as $vendorId) {
            try {
                $vendor = Vendor::findOrFail($vendorId);
                $wallet = VendorWallet::where('vendor_id', $vendorId)->first();

                if (!$wallet || $wallet->status !== 'active' || $wallet->pending_amount <= 0) {
                    $this->logFailure($vendor, 'Wallet inactive or insufficient balance');
                    continue;
                }

                $bankAccount = $vendor->primaryBankAccount;
                if (!$bankAccount || !$bankAccount->razorpay_fund_account_id) {
                    $this->logFailure($vendor, 'No valid bank account found');
                    continue;
                }

                // Create the payout record
                $payout = VendorPayout::create([
                    'vendor_id' => $vendorId,
                    'amount' => $wallet->pending_amount,
                    'status' => VendorPayout::STATUS_PENDING,
                    'payment_method' => 'razorpayx',
                    'payout_mode' => $this->payoutMode,
                    'notes' => $this->notes ?? 'Bulk payout',
                    'is_automated' => true,
                    'requested_at' => now(),
                    'processed_by' => $this->userId,
                ]);

                // Process the payout via RazorpayX
                $result = $razorpayXService->processPayout($payout, $this->payoutMode);

                if ($result['success']) {
                    // Refresh payout to get updated status from database
                    $payout->refresh();
                    
                    // Update wallet balance when payout is processing or completed
                    // (money has been sent to RazorpayX, deduct from pending)
                    if (in_array($payout->status, [VendorPayout::STATUS_PROCESSING, VendorPayout::STATUS_COMPLETED])) {
                        $wallet->recordPayout($payout->amount);
                        Log::info('Wallet updated for vendor ' . $vendorId, [
                            'payout_status' => $payout->status,
                            'amount' => $payout->amount,
                            'new_pending' => $wallet->fresh()->pending_amount
                        ]);
                    }

                    $this->logSuccess($vendor, $payout->amount);
                    $this->notifyVendor($vendor, $payout);
                } else {
                    $this->logFailure($vendor, 'RazorpayX API error: ' . ($result['error'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                Log::error('Error processing payout for vendor ID ' . $vendorId . ': ' . $e->getMessage());
                $this->logFailure(Vendor::find($vendorId), 'Exception: ' . $e->getMessage());
            }
        }

        Log::info('Bulk payout process completed', $this->results);
    }

    /**
     * Log a successful payout.
     */
    protected function logSuccess(Vendor $vendor, float $amount): void
    {
        $this->results['success']++;
        $this->results['amount'] += $amount;
        $this->results['details'][] = [
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'amount' => $amount,
            'status' => 'success'
        ];
    }

    /**
     * Log a failed payout.
     */
    protected function logFailure(Vendor $vendor, string $reason): void
    {
        $this->results['failed']++;
        $this->results['details'][] = [
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'status' => 'failed',
            'reason' => $reason
        ];
    }

    /**
     * Map RazorpayX status to internal status.
     */
    protected function mapRazorpayXStatus(string $status): string
    {
        return match ($status) {
            'processed' => VendorPayout::STATUS_COMPLETED,
            'processing' => VendorPayout::STATUS_PROCESSING,
            'pending' => VendorPayout::STATUS_PENDING,
            'queued' => VendorPayout::STATUS_PENDING,
            'rejected' => VendorPayout::STATUS_REJECTED,
            'reversed' => VendorPayout::STATUS_REVERSED,
            'failed' => VendorPayout::STATUS_FAILED,
            default => VendorPayout::STATUS_PENDING,
        };
    }

    /**
     * Notify the vendor about the payout.
     */
    protected function notifyVendor(Vendor $vendor, VendorPayout $payout): void
    {
        try {
            $user = User::where('vendor_id', $vendor->id)->first();
            if ($user) {
                $user->notify(new PayoutProcessed($payout));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send payout notification to vendor ' . $vendor->id . ': ' . $e->getMessage());
        }
    }
}
