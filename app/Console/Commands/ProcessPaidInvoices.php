<?php

namespace App\Console\Commands;

use App\Models\ProformaInvoice;
use App\Models\VendorEarning;
use App\Services\InvoicePaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPaidInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:process-paid {--all : Process all paid invoices including already approved ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all paid invoices that have not been approved yet or missing vendor earnings';

    /**
     * The invoice payment service.
     *
     * @var InvoicePaymentService
     */
    protected $invoicePaymentService;

    /**
     * Create a new command instance.
     *
     * @param InvoicePaymentService $invoicePaymentService
     * @return void
     */
    public function __construct(InvoicePaymentService $invoicePaymentService)
    {
        parent::__construct();
        $this->invoicePaymentService = $invoicePaymentService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Processing paid invoices...');

        // Get IDs of invoices that already have vendor earnings
        $invoicesWithEarnings = VendorEarning::pluck('invoice_id')->toArray();

        // Build query for paid invoices
        $query = ProformaInvoice::where('payment_status', 'paid')
            ->whereNotNull('vendor_id');

        if ($this->option('all')) {
            // Process ALL paid invoices that don't have vendor earnings yet
            $this->info('Processing ALL paid invoices (including approved ones)...');
            $query->whereNotIn('id', $invoicesWithEarnings);
        } else {
            // Default: Process paid invoices that are not approved OR don't have vendor earnings
            $query->where(function ($q) use ($invoicesWithEarnings) {
                $q->where('status', '!=', ProformaInvoice::STATUS_APPROVED)
                  ->orWhereNotIn('id', $invoicesWithEarnings);
            });
        }

        $invoices = $query->get();

        $count = $invoices->count();
        $this->info("Found {$count} paid invoices that need processing.");

        if ($count === 0) {
            $this->info('No invoices to process.');
            return Command::SUCCESS;
        }

        $processed = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $this->info("Processing invoice #{$invoice->invoice_number} (Vendor ID: {$invoice->vendor_id})");
            
            try {
                $result = $this->invoicePaymentService->processInvoicePayment($invoice);
                
                if ($result) {
                    $processed++;
                    $this->info("Successfully processed invoice #{$invoice->invoice_number}");
                } else {
                    $failed++;
                    $this->error("Failed to process invoice #{$invoice->invoice_number}");
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('Error processing invoice', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
                $this->error("Error processing invoice #{$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        $this->info("Processed {$processed} invoices successfully. Failed: {$failed}");
        return Command::SUCCESS;
    }
}