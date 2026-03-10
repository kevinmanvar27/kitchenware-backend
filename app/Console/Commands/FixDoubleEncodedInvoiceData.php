<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProformaInvoice;
use Illuminate\Support\Facades\DB;

class FixDoubleEncodedInvoiceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:fix-encoding {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix double-encoded invoice_data in proforma_invoices table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in dry-run mode - no changes will be made.');
        }
        
        $this->info('Scanning proforma invoices for double-encoded data...');
        
        // Get all invoices - we need to check the raw data
        $invoices = DB::table('proforma_invoices')->get();
        
        $fixed = 0;
        $skipped = 0;
        
        foreach ($invoices as $invoice) {
            $rawData = $invoice->invoice_data;
            
            // Check if the data is a JSON string that contains another JSON string
            // This happens when json_encode was called on already-encoded data
            if (is_string($rawData)) {
                $decoded = json_decode($rawData, true);
                
                // If after first decode we get a string, it was double-encoded
                if (is_string($decoded)) {
                    $this->line("Invoice #{$invoice->id} ({$invoice->invoice_number}): Double-encoded data detected");
                    
                    // Decode again to get the actual array
                    $actualData = json_decode($decoded, true);
                    
                    // Check for triple-encoding edge case
                    if (is_string($actualData)) {
                        $actualData = json_decode($actualData, true);
                        $this->warn("  -> Triple-encoded! Decoded 3 times.");
                    }
                    
                    if (is_array($actualData)) {
                        if (!$dryRun) {
                            // Update the record with properly encoded data
                            DB::table('proforma_invoices')
                                ->where('id', $invoice->id)
                                ->update(['invoice_data' => json_encode($actualData)]);
                            
                            $this->info("  -> Fixed successfully!");
                        } else {
                            $this->info("  -> Would be fixed (dry-run)");
                        }
                        $fixed++;
                    } else {
                        $this->error("  -> Could not decode to array, skipping");
                        $skipped++;
                    }
                } elseif (is_array($decoded)) {
                    // Already properly encoded
                    $skipped++;
                } else {
                    $this->warn("Invoice #{$invoice->id}: Unknown data format, skipping");
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info("Summary:");
        $this->info("  - Fixed: {$fixed}");
        $this->info("  - Skipped (already correct): {$skipped}");
        
        if ($dryRun && $fixed > 0) {
            $this->newLine();
            $this->warn("Run without --dry-run to apply fixes.");
        }
        
        return Command::SUCCESS;
    }
}
