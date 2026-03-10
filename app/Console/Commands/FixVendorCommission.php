<?php

namespace App\Console\Commands;

use App\Models\Vendor;
use App\Models\VendorEarning;
use App\Models\VendorWallet;
use Illuminate\Console\Command;

class FixVendorCommission extends Command
{
    protected $signature = 'vendors:fix-commission {email} {rate=10}';
    protected $description = 'Fix vendor commission rate and reset earnings for recalculation';

    public function handle()
    {
        $email = $this->argument('email');
        $rate = $this->argument('rate');

        $vendor = Vendor::whereHas('user', function($q) use ($email) {
            $q->where('email', $email);
        })->first();

        if (!$vendor) {
            $this->error("Vendor with email {$email} not found!");
            return 1;
        }

        $this->info("Found vendor: {$email}");
        $this->info("Current commission rate: {$vendor->commission_rate}%");

        // Update commission rate
        $vendor->update(['commission_rate' => $rate]);
        $this->info("Updated commission rate to: {$rate}%");

        // Delete earnings for this vendor
        $deleted = VendorEarning::where('vendor_id', $vendor->id)->delete();
        $this->info("Deleted {$deleted} earning records");

        // Reset wallet
        VendorWallet::where('vendor_id', $vendor->id)->update([
            'total_earned' => 0,
            'pending_amount' => 0,
            'total_paid' => 0
        ]);
        $this->info("Reset wallet to zero");

        $this->info("Done! Now run: php artisan vendors:sync-earnings");

        return 0;
    }
}