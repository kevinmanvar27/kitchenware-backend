<?php

namespace App\Console\Commands;

use App\Models\ProformaInvoice;
use App\Models\Vendor;
use App\Models\VendorEarning;
use App\Models\VendorWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncVendorEarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendors:sync-earnings {--vendor= : Specific vendor ID to sync} {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync vendor earnings from all paid invoices (one-time fix for missing earnings)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $vendorId = $this->option('vendor');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->info('Syncing vendor earnings from paid invoices...');

        // Get all paid invoices with vendor_id
        $query = ProformaInvoice::where('payment_status', 'paid')
            ->whereNotNull('vendor_id');

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
            $this->info("Filtering for vendor ID: {$vendorId}");
        }

        $invoices = $query->get();
        $this->info("Found {$invoices->count()} paid invoices with vendors.");

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($invoices as $invoice) {
            try {
                $vendor = Vendor::find($invoice->vendor_id);
                
                if (!$vendor) {
                    $this->warn("Vendor ID {$invoice->vendor_id} not found for invoice #{$invoice->invoice_number}");
                    $errors++;
                    continue;
                }

                // Check if earning already exists
                $existingEarning = VendorEarning::where('invoice_id', $invoice->id)->first();

                $commissionRate = $vendor->commission_rate ?? 0;
                $commissionAmount = ($invoice->total_amount * $commissionRate) / 100;
                $vendorEarningAmount = $invoice->total_amount - $commissionAmount;

                $vendorEmail = $vendor->user->email ?? 'N/A';
                $this->line("Invoice #{$invoice->invoice_number}:");
                $this->line("  - Vendor: {$vendorEmail}");
                $this->line("  - Total: ₹{$invoice->total_amount}");
                $this->line("  - Commission Rate: {$commissionRate}%");
                $this->line("  - Commission Amount: ₹{$commissionAmount}");
                $this->line("  - Vendor Earning: ₹{$vendorEarningAmount}");

                if ($existingEarning) {
                    if ($existingEarning->status === VendorEarning::STATUS_PAID) {
                        $this->info("  - Status: Already processed (PAID)");
                        $skipped++;
                        continue;
                    }
                    
                    $this->info("  - Status: Existing earning found, updating...");
                    
                    if (!$dryRun) {
                        DB::beginTransaction();
                        try {
                            $existingEarning->update([
                                'status' => VendorEarning::STATUS_PAID
                            ]);

                            // Update wallet
                            $wallet = VendorWallet::firstOrCreate(
                                ['vendor_id' => $invoice->vendor_id],
                                [
                                    'total_earned' => 0,
                                    'total_paid' => 0,
                                    'pending_amount' => 0,
                                    'hold_amount' => 0,
                                    'status' => VendorWallet::STATUS_ACTIVE
                                ]
                            );
                            $wallet->addEarning($existingEarning->vendor_earning);
                            
                            DB::commit();
                            $updated++;
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    } else {
                        $updated++;
                    }
                } else {
                    $this->info("  - Status: Creating new earning...");
                    
                    if (!$dryRun) {
                        DB::beginTransaction();
                        try {
                            $vendorEarning = VendorEarning::create([
                                'vendor_id' => $invoice->vendor_id,
                                'invoice_id' => $invoice->id,
                                'order_amount' => $invoice->total_amount,
                                'commission_rate' => $commissionRate,
                                'commission_amount' => $commissionAmount,
                                'vendor_earning' => $vendorEarningAmount,
                                'status' => VendorEarning::STATUS_PAID,
                                'description' => 'Invoice #' . $invoice->invoice_number,
                            ]);

                            // Update wallet
                            $wallet = VendorWallet::firstOrCreate(
                                ['vendor_id' => $invoice->vendor_id],
                                [
                                    'total_earned' => 0,
                                    'total_paid' => 0,
                                    'pending_amount' => 0,
                                    'hold_amount' => 0,
                                    'status' => VendorWallet::STATUS_ACTIVE
                                ]
                            );
                            $wallet->addEarning($vendorEarningAmount);
                            
                            DB::commit();
                            $created++;
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    } else {
                        $created++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error processing invoice #{$invoice->invoice_number}: {$e->getMessage()}");
                Log::error('Error syncing vendor earning', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Created: {$created}");
        $this->info("Updated: {$updated}");
        $this->info("Skipped: {$skipped}");
        $this->info("Errors: {$errors}");

        if ($dryRun) {
            $this->warn('DRY RUN - No actual changes were made. Run without --dry-run to apply changes.');
        }

        // Show wallet summary
        $this->newLine();
        $this->info('=== Vendor Wallet Summary ===');
        $wallets = VendorWallet::with('vendor.user')->get();
        
        foreach ($wallets as $wallet) {
            $email = optional($wallet->vendor)->user->email ?? 'N/A';
            $this->line("Vendor: {$email}");
            $this->line("  - Total Earned: ₹{$wallet->total_earned}");
            $this->line("  - Pending: ₹{$wallet->pending_amount}");
            $this->line("  - Total Paid: ₹{$wallet->total_paid}");
        }

        return Command::SUCCESS;
    }
}