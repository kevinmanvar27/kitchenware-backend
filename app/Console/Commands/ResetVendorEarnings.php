<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Models\VendorEarning;
use App\Models\VendorWallet;
use Illuminate\Console\Command;

class ResetVendorEarnings extends Command
{
    protected $signature = 'vendors:reset-earnings {--commission=10 : Set commission rate for all vendors}';
    protected $description = 'Reset all vendor earnings and wallets, fix commission rates';

    public function handle()
    {
        $commissionRate = $this->option('commission');

        $this->info("Resetting all vendor earnings...");

        // Update all vendors commission rate
        $vendors = Vendor::all();
        foreach ($vendors as $vendor) {
            $email = $vendor->user->email ?? 'Unknown';
            $oldRate = $vendor->commission_rate;
            
            $vendor->update(['commission_rate' => $commissionRate]);
            $this->line("Vendor {$email}: Commission {$oldRate}% -> {$commissionRate}%");
        }

        // Delete all earnings
        $deletedEarnings = VendorEarning::count();
        VendorEarning::truncate();
        $this->info("Deleted {$deletedEarnings} earning records");

        // Reset all wallets
        VendorWallet::query()->update([
            'total_earned' => 0,
            'pending_amount' => 0,
            'total_paid' => 0
        ]);
        $this->info("Reset all wallets to zero");

        $this->newLine();
        $this->info("Done! Now run: php artisan vendors:sync-earnings");

        return 0;
    }
}