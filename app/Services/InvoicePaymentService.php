<?php

namespace App\Services;

use App\Models\ProformaInvoice;
use App\Models\VendorEarning;
use App\Models\VendorWallet;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicePaymentService
{
    /**
     * Update invoice status when payment is marked as paid
     * and update vendor wallet with the earned amount
     *
     * @param ProformaInvoice $invoice
     * @return bool
     */
    public function processInvoicePayment(ProformaInvoice $invoice): bool
    {
        try {
            DB::beginTransaction();
            
            // Check if payment status is paid
            if ($invoice->payment_status === 'paid') {
                // Update invoice status to Approved if it's not already
                if ($invoice->status !== ProformaInvoice::STATUS_APPROVED) {
                    $invoice->update([
                        'status' => ProformaInvoice::STATUS_APPROVED
                    ]);
                    
                    Log::info('Invoice status updated to Approved', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number
                    ]);
                }
                
                // Check if vendor_id exists
                if (!$invoice->vendor_id) {
                    Log::warning('Invoice has no vendor_id', [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number
                    ]);
                    DB::commit();
                    return false;
                }
                
                // Find or create vendor earning record
                $vendorEarning = VendorEarning::where('invoice_id', $invoice->id)->first();
                
                if (!$vendorEarning) {
                    // Create vendor earning if it doesn't exist
                    $vendor = Vendor::find($invoice->vendor_id);
                    
                    if ($vendor) {
                        $commissionRate = $vendor->commission_rate ?? 0;
                        $commissionAmount = ($invoice->total_amount * $commissionRate) / 100;
                        $vendorEarningAmount = $invoice->total_amount - $commissionAmount;
                        
                        $vendorEarning = VendorEarning::create([
                            'vendor_id' => $invoice->vendor_id,
                            'invoice_id' => $invoice->id,
                            'order_amount' => $invoice->total_amount,
                            'commission_rate' => $commissionRate,
                            'commission_amount' => $commissionAmount,
                            'vendor_earning' => $vendorEarningAmount,
                            'status' => VendorEarning::STATUS_PENDING,
                            'description' => 'Invoice #' . $invoice->invoice_number,
                        ]);
                        
                        Log::info('Vendor earning created during payment processing', [
                            'invoice_id' => $invoice->id,
                            'vendor_id' => $invoice->vendor_id,
                            'amount' => $vendorEarningAmount
                        ]);
                    } else {
                        Log::warning('Vendor not found for invoice', [
                            'invoice_id' => $invoice->id,
                            'vendor_id' => $invoice->vendor_id
                        ]);
                        DB::commit();
                        return false;
                    }
                }
                
                // Update vendor earning status if not already paid
                if ($vendorEarning && $vendorEarning->status !== VendorEarning::STATUS_PAID) {
                    $vendorEarning->update([
                        'status' => VendorEarning::STATUS_PAID
                    ]);
                    
                    // Update vendor wallet
                    $vendorWallet = VendorWallet::firstOrCreate(
                        ['vendor_id' => $invoice->vendor_id],
                        [
                            'total_earned' => 0,
                            'total_paid' => 0,
                            'pending_amount' => 0,
                            'hold_amount' => 0,
                            'status' => VendorWallet::STATUS_ACTIVE
                        ]
                    );
                    
                    // Add the earning to the wallet
                    $vendorWallet->addEarning($vendorEarning->vendor_earning);
                    
                    Log::info('Vendor earning updated and added to wallet', [
                        'vendor_id' => $invoice->vendor_id,
                        'invoice_id' => $invoice->id,
                        'amount' => $vendorEarning->vendor_earning,
                        'wallet_total' => $vendorWallet->total_earned,
                        'wallet_pending' => $vendorWallet->pending_amount
                    ]);
                } else {
                    Log::info('Vendor earning already processed', [
                        'invoice_id' => $invoice->id,
                        'vendor_id' => $invoice->vendor_id,
                        'status' => $vendorEarning ? $vendorEarning->status : 'not found'
                    ]);
                }
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing invoice payment', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Process payment status update for an invoice
     *
     * @param ProformaInvoice $invoice
     * @param string $paymentStatus
     * @param float $paidAmount
     * @return bool
     */
    public function updatePaymentStatus(ProformaInvoice $invoice, string $paymentStatus, float $paidAmount): bool
    {
        try {
            DB::beginTransaction();
            
            $invoice->update([
                'payment_status' => $paymentStatus,
                'paid_amount' => $paidAmount
            ]);
            
            // If payment is marked as paid, process the invoice
            if ($paymentStatus === 'paid') {
                $this->processInvoicePayment($invoice);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating payment status', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}