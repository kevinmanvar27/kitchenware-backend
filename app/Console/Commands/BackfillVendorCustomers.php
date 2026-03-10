<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProformaInvoice;
use App\Models\VendorCustomer;
use Illuminate\Support\Facades\DB;

class BackfillVendorCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendor:backfill-customers {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill vendor_customers table from existing proforma invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Scanning existing proforma invoices for vendor-customer relationships...');
        
        // Get all invoices that have both user_id and vendor_id
        $invoices = ProformaInvoice::whereNotNull('user_id')
            ->whereNotNull('vendor_id')
            ->orderBy('created_at', 'asc') // Process oldest first to get correct first_invoice_id
            ->get(['id', 'user_id', 'vendor_id', 'created_at']);
        
        $this->info("Found {$invoices->count()} invoices with user and vendor.");
        
        $created = 0;
        $skipped = 0;
        
        foreach ($invoices as $invoice) {
            // Check if relationship already exists
            $exists = VendorCustomer::where('vendor_id', $invoice->vendor_id)
                ->where('user_id', $invoice->user_id)
                ->exists();
            
            if ($exists) {
                $skipped++;
                continue;
            }
            
            if ($dryRun) {
                $this->line("Would create: Vendor #{$invoice->vendor_id} - User #{$invoice->user_id} (Invoice #{$invoice->id})");
                $created++;
            } else {
                VendorCustomer::create([
                    'vendor_id' => $invoice->vendor_id,
                    'user_id' => $invoice->user_id,
                    'first_invoice_id' => $invoice->id,
                ]);
                $created++;
            }
        }
        
        if ($dryRun) {
            $this->info("Dry run complete. Would create {$created} vendor-customer relationships. Skipped {$skipped} existing.");
        } else {
            $this->info("Backfill complete. Created {$created} vendor-customer relationships. Skipped {$skipped} existing.");
        }
        
        return Command::SUCCESS;
    }
}
