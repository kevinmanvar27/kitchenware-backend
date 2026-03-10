<?php

namespace App\Observers;

use App\Models\ProformaInvoice;
use App\Models\Vendor;
use App\Models\VendorEarning;
use App\Services\InvoicePaymentService;
use Illuminate\Support\Facades\Log;

class ProformaInvoiceObserver
{
    protected $invoicePaymentService;

    public function __construct(InvoicePaymentService $invoicePaymentService)
    {
        $this->invoicePaymentService = $invoicePaymentService;
    }

    /**
     * Handle the ProformaInvoice "created" event.
     *
     * @param  \App\Models\ProformaInvoice  $invoice
     * @return void
     */
    public function created(ProformaInvoice $invoice)
    {
        // Only create earnings record if vendor_id is set
        if ($invoice->vendor_id) {
            $vendor = Vendor::find($invoice->vendor_id);
            
            if ($vendor) {
                try {
                    // Calculate commission
                    $commissionRate = $vendor->commission_rate ?? 0;
                    $commissionAmount = ($invoice->total_amount * $commissionRate) / 100;
                    $vendorEarning = $invoice->total_amount - $commissionAmount;
                    
                    // Create vendor earning record
                    VendorEarning::create([
                        'vendor_id' => $invoice->vendor_id,
                        'invoice_id' => $invoice->id,
                        'order_amount' => $invoice->total_amount,
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                        'vendor_earning' => $vendorEarning,
                        'status' => 'pending', // Will be updated to 'paid' when payment is completed
                        'description' => 'Invoice #' . $invoice->invoice_number,
                    ]);
                    
                    Log::info('Vendor earning record created', [
                        'invoice_id' => $invoice->id,
                        'vendor_id' => $vendor->id,
                        'amount' => $vendorEarning
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create vendor earning record', [
                        'invoice_id' => $invoice->id,
                        'vendor_id' => $vendor->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Handle the ProformaInvoice "updated" event.
     *
     * @param  \App\Models\ProformaInvoice  $invoice
     * @return void
     */
    public function updated(ProformaInvoice $invoice)
    {
        // If payment status was changed to paid, process the invoice
        if ($invoice->isDirty('payment_status') && $invoice->payment_status === 'paid') {
            $this->invoicePaymentService->processInvoicePayment($invoice);
        }
    }
}