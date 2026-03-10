<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Models\VendorWallet;
use App\Services\RazorpayXService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessVendorPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendors:process-payouts 
                            {--vendor= : Process payout for a specific vendor ID}
                            {--min-amount=100 : Minimum amount required for payout}
                            {--mode=NEFT : Payout mode (NEFT, IMPS, RTGS)}
                            {--dry-run : Run without actually processing payouts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending vendor payouts via RazorpayX';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting vendor payout processing...');
        $this->newLine();

        $vendorId = $this->option('vendor');
        $minAmount = (float) $this->option('min-amount');
        $payoutMode = strtoupper($this->option('mode'));
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No actual payouts will be processed');
            $this->newLine();
        }

        // Validate payout mode
        if (!in_array($payoutMode, ['NEFT', 'IMPS', 'RTGS'])) {
            $this->error('Invalid payout mode. Use NEFT, IMPS, or RTGS.');
            return 1;
        }

        // Get vendors with pending payments
        $query = Vendor::query()
            ->with(['wallet', 'primaryBankAccount', 'user'])
            ->whereHas('wallet', function ($q) use ($minAmount) {
                $q->where('status', 'active')
                  ->whereRaw('(pending_amount - hold_amount) >= ?', [$minAmount]);
            })
            ->whereHas('primaryBankAccount');

        if ($vendorId) {
            $query->where('id', $vendorId);
        }

        $vendors = $query->get();

        if ($vendors->isEmpty()) {
            $this->info('No vendors with pending payouts found.');
            return 0;
        }

        $this->info("Found {$vendors->count()} vendor(s) eligible for payout");
        $this->newLine();

        // Display summary table
        $tableData = $vendors->map(function ($vendor) {
            return [
                'ID' => $vendor->id,
                'Store' => $vendor->store_name,
                'Pending' => '₹' . number_format($vendor->wallet->pending_amount, 2),
                'Payable' => '₹' . number_format($vendor->wallet->payable_amount, 2),
                'Bank Status' => $vendor->primaryBankAccount->hasFundAccount() ? 'Ready' : 'Setup Required',
            ];
        })->toArray();

        $this->table(['ID', 'Store', 'Pending', 'Payable', 'Bank Status'], $tableData);
        $this->newLine();

        if (!$dryRun && !$this->confirm('Do you want to proceed with processing these payouts?', true)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Initialize RazorpayX service
        $razorpayService = app(RazorpayXService::class);

        // Check RazorpayX balance first
        $this->info('Checking RazorpayX account balance...');
        $balanceResult = $razorpayService->getBalance();
        
        if (!$balanceResult['success']) {
            $this->error('Failed to fetch RazorpayX balance: ' . ($balanceResult['error'] ?? 'Unknown error'));
            return 1;
        }

        $availableBalance = $balanceResult['data']['balance_rupees'] ?? 0;
        $this->info("Available balance: ₹" . number_format($availableBalance, 2));
        $this->newLine();

        // Calculate total payout amount
        $totalPayoutAmount = $vendors->sum(fn($v) => $v->wallet->payable_amount);
        
        if ($totalPayoutAmount > $availableBalance) {
            $this->warn("Warning: Total payout amount (₹" . number_format($totalPayoutAmount, 2) . ") exceeds available balance.");
            if (!$this->confirm('Continue with partial payouts?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Process payouts
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        $progressBar = $this->output->createProgressBar($vendors->count());
        $progressBar->start();

        foreach ($vendors as $vendor) {
            $wallet = $vendor->wallet;
            $bankAccount = $vendor->primaryBankAccount;
            $payableAmount = $wallet->payable_amount;

            // Skip if amount is below minimum
            if ($payableAmount < $minAmount) {
                $skippedCount++;
                Log::info("Skipping vendor {$vendor->id}: Amount below minimum", [
                    'vendor_id' => $vendor->id,
                    'payable_amount' => $payableAmount,
                    'min_amount' => $minAmount,
                ]);
                $progressBar->advance();
                continue;
            }

            // Skip if insufficient balance
            if ($payableAmount > $availableBalance) {
                $skippedCount++;
                Log::info("Skipping vendor {$vendor->id}: Insufficient RazorpayX balance", [
                    'vendor_id' => $vendor->id,
                    'payable_amount' => $payableAmount,
                    'available_balance' => $availableBalance,
                ]);
                $progressBar->advance();
                continue;
            }

            if ($dryRun) {
                $this->newLine();
                $this->info("  [DRY RUN] Would process payout of ₹" . number_format($payableAmount, 2) . " to {$vendor->store_name}");
                $successCount++;
                $progressBar->advance();
                continue;
            }

            // Process the payout
            try {
                $result = $razorpayService->processPayout(
                    $vendor,
                    $payableAmount,
                    $payoutMode,
                    "Monthly payout - " . now()->format('F Y')
                );

                if ($result['success']) {
                    $successCount++;
                    $availableBalance -= $payableAmount; // Update available balance
                    
                    Log::info("Payout successful for vendor {$vendor->id}", [
                        'vendor_id' => $vendor->id,
                        'amount' => $payableAmount,
                        'payout_id' => $result['payout']->razorpay_payout_id ?? null,
                    ]);
                } else {
                    $failedCount++;
                    
                    Log::error("Payout failed for vendor {$vendor->id}", [
                        'vendor_id' => $vendor->id,
                        'amount' => $payableAmount,
                        'error' => $result['error'] ?? 'Unknown error',
                    ]);
                }
            } catch (\Exception $e) {
                $failedCount++;
                
                Log::error("Exception during payout for vendor {$vendor->id}", [
                    'vendor_id' => $vendor->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Payout Processing Complete');
        $this->table(
            ['Status', 'Count'],
            [
                ['Successful', $successCount],
                ['Failed', $failedCount],
                ['Skipped', $skippedCount],
                ['Total', $vendors->count()],
            ]
        );

        if ($failedCount > 0) {
            $this->warn("Check logs for details on failed payouts.");
        }

        return 0;
    }
}
