<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Models\VendorPayout;
use App\Services\MockRazorpayXService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestVendorPayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:vendor-payout {vendor_id?} {amount=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test vendor payout functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting vendor payout test...');
        
        // Get vendor ID from argument or use the first vendor
        $vendorId = $this->argument('vendor_id');
        $amount = $this->argument('amount');
        
        if (!$vendorId) {
            $vendor = Vendor::first();
            if (!$vendor) {
                $this->error('No vendors found in the system.');
                return 1;
            }
            $vendorId = $vendor->id;
        } else {
            $vendor = Vendor::find($vendorId);
            if (!$vendor) {
                $this->error("Vendor with ID {$vendorId} not found.");
                return 1;
            }
        }
        
        $this->info("Testing payout for vendor: {$vendor->store_name} (ID: {$vendor->id})");
        
        // Check if vendor has a wallet
        $wallet = $vendor->wallet;
        if (!$wallet) {
            $this->warn("Vendor doesn't have a wallet. Creating one...");
            $wallet = $vendor->getOrCreateWallet();
            $this->info("Wallet created successfully.");
        }
        
        // Check if wallet is active
        if ($wallet->status !== 'active') {
            $this->warn("Vendor wallet is not active. Activating...");
            $wallet->update(['status' => 'active']);
            $this->info("Wallet activated successfully.");
        }
        
        // Check if vendor has enough balance
        if ($wallet->pending_amount < $amount) {
            $this->warn("Vendor doesn't have enough balance. Adding funds...");
            $wallet->increment('total_earned', $amount);
            $wallet->increment('pending_amount', $amount);
            $this->info("Added ₹{$amount} to vendor wallet.");
        }
        
        // Check if vendor has a bank account
        $bankAccount = $vendor->primaryBankAccount;
        if (!$bankAccount) {
            $this->error("Vendor doesn't have a primary bank account. Please set up a bank account first.");
            return 1;
        }
        
        $this->info("Bank account found: {$bankAccount->bank_name} - {$bankAccount->masked_account_number}");
        
        // Create a mock RazorpayX service
        $razorpayService = new MockRazorpayXService();
        
        // Setup bank account for RazorpayX if not already set up
        if (!$bankAccount->hasFundAccount()) {
            $this->info("Setting up bank account with RazorpayX...");
            $setupResult = $razorpayService->setupVendorForPayouts($vendor, $bankAccount);
            
            if (!$setupResult['success']) {
                $this->error("Failed to setup bank account: {$setupResult['error']}");
                return 1;
            }
            
            $this->info("Bank account setup successfully.");
        }
        
        // Create payout
        $this->info("Creating payout of ₹{$amount}...");
        
        DB::beginTransaction();
        try {
            // Create payout record
            $payout = VendorPayout::create([
                'vendor_id' => $vendor->id,
                'amount' => $amount,
                'status' => VendorPayout::STATUS_PENDING,
                'payment_method' => 'razorpayx',
                'payout_mode' => 'NEFT',
                'notes' => 'Test payout',
                'requested_at' => now(),
                'is_automated' => false,
                'bank_details' => [
                    'account_number' => $bankAccount->masked_account_number,
                    'ifsc_code' => $bankAccount->ifsc_code,
                    'bank_name' => $bankAccount->bank_name,
                    'account_holder' => $bankAccount->account_holder_name,
                ],
            ]);
            
            // Process payout
            $result = $razorpayService->processPayout($payout);
            
            if (!$result['success']) {
                DB::rollBack();
                $this->error("Payout failed: {$result['error']}");
                return 1;
            }
            
            // Update wallet
            $wallet->recordPayout($amount);
            
            DB::commit();
            
            $this->info("Payout created successfully!");
            $this->info("Payout ID: {$payout->id}");
            $this->info("RazorpayX Payout ID: {$payout->razorpay_payout_id}");
            $this->info("Status: {$payout->status}");
            
            // Sync payout status
            $this->info("Syncing payout status...");
            $syncResult = $razorpayService->getPayoutStatus($payout->razorpay_payout_id);
            
            if ($syncResult['success']) {
                $razorpayData = $syncResult['data'];
                $newStatus = $this->mapRazorpayStatus($razorpayData['status']);
                
                $payout->update([
                    'status' => $newStatus,
                    'razorpay_status' => $razorpayData['status'],
                    'utr' => $razorpayData['utr'] ?? $payout->utr,
                    'processed_at' => now(),
                ]);
                
                $this->info("Payout status updated: {$newStatus}");
            } else {
                $this->warn("Failed to sync payout status: {$syncResult['error']}");
            }
            
            // Display final wallet balance
            $wallet->refresh();
            $this->info("Final wallet balance:");
            $this->info("Total earned: ₹{$wallet->total_earned}");
            $this->info("Total paid: ₹{$wallet->total_paid}");
            $this->info("Pending amount: ₹{$wallet->pending_amount}");
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Exception occurred: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
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
}