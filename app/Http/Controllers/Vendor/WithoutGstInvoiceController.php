<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithoutGstInvoice;
use App\Models\ProformaInvoice;
use App\Models\Notification;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\LogsActivity;

class WithoutGstInvoiceController extends Controller
{
    use LogsActivity;
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display a listing of without GST invoices for the vendor.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        $withoutGstInvoices = WithoutGstInvoice::with('user')
            ->where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Remove all without_gst_invoice notifications when visiting this page
        Notification::where('user_id', Auth::id())
            ->where('type', 'without_gst_invoice')
            ->delete();
        
        return view('vendor.invoices-black.index', compact('withoutGstInvoices'));
    }
    
    /**
     * Display the specified without GST invoice.
     */
    public function show($id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::with('user')
            ->where('vendor_id', $vendor->id)
            ->findOrFail($id);
        
        $invoiceData = $invoice->invoice_data;
        
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? 0;
        $invoiceDate = $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;
        $invoiceNumber = $invoice->invoice_number;
        
        // Remove notifications for this invoice
        Notification::where('user_id', Auth::id())
            ->where('type', 'without_gst_invoice')
            ->where('data', 'like', '%"invoice_id":' . $id . '%')
            ->delete();
        
        return view('vendor.invoices-black.show', compact('invoice', 'cartItems', 'total', 'invoiceNumber', 'invoiceDate', 'customer', 'invoiceData'));
    }
    
    /**
     * Update the without GST invoice.
     */
    public function update(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $invoiceData = $invoice->invoice_data;
        
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Update status if provided
        $status = $invoice->status;
        if ($request->has('status')) {
            $request->validate([
                'status' => 'required|in:' . implode(',', WithoutGstInvoice::STATUS_OPTIONS)
            ]);
            $status = $request->input('status');
            $invoice->status = $status;
        }
        
        // Update cart items if provided
        $subtotal = 0;
        if ($request->has('items')) {
            $items = $request->input('items');
            $cartItems = [];
            
            foreach ($items as $index => $item) {
                $originalItem = $invoiceData['cart_items'][$index] ?? [];
                
                // Build updated item with price/quantity from request
                $updatedItem = [
                    'product_name' => $originalItem['product_name'] ?? 'Product',
                    'product_description' => $originalItem['product_description'] ?? '',
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'total' => (float) $item['total'],
                ];
                
                // Preserve variation data if it exists in original item
                if (isset($originalItem['product_variation_id'])) {
                    $updatedItem['product_variation_id'] = $originalItem['product_variation_id'];
                }
                if (isset($originalItem['variation_display_name'])) {
                    $updatedItem['variation_display_name'] = $originalItem['variation_display_name'];
                }
                if (isset($originalItem['variation_attributes'])) {
                    $updatedItem['variation_attributes'] = $originalItem['variation_attributes'];
                }
                if (isset($originalItem['variation_sku'])) {
                    $updatedItem['variation_sku'] = $originalItem['variation_sku'];
                }
                
                $cartItems[] = $updatedItem;
                $subtotal += (float) $item['total'];
            }
            
            $invoiceData['cart_items'] = $cartItems;
        } else {
            // Calculate subtotal from existing cart items
            foreach ($invoiceData['cart_items'] ?? [] as $item) {
                $subtotal += floatval($item['total'] ?? 0);
            }
        }
        
        // Get previous and new GST type
        $previousGstType = $invoiceData['gst_type'] ?? 'without_gst';
        $newGstType = $request->gst_type ?? $previousGstType;
        
        // Get GST type and calculate accordingly
        $taxPercentage = $newGstType === 'without_gst' ? 0 : floatval($request->tax_percentage ?? 18);
        $taxAmount = $newGstType === 'without_gst' ? 0 : ($subtotal * $taxPercentage / 100);
        $shipping = floatval($request->shipping ?? $invoiceData['shipping'] ?? 0);
        $discountAmount = floatval($request->discount_amount ?? $invoiceData['discount_amount'] ?? 0);
        $couponDiscount = floatval($invoiceData['coupon_discount'] ?? 0);
        
        // Calculate total
        $total = ($subtotal + $shipping + $taxAmount) - $discountAmount - $couponDiscount;
        
        // Update invoice data
        $invoiceData['subtotal'] = $subtotal;
        $invoiceData['discount_percentage'] = (float) $request->input('discount_percentage', $invoiceData['discount_percentage'] ?? 0);
        $invoiceData['discount_amount'] = $discountAmount;
        $invoiceData['shipping'] = $shipping;
        $invoiceData['gst_type'] = $newGstType;
        $invoiceData['tax_percentage'] = $taxPercentage;
        $invoiceData['tax_amount'] = $taxAmount;
        $invoiceData['total'] = $total;
        $invoiceData['notes'] = $request->input('notes', $invoiceData['notes'] ?? ($newGstType === 'without_gst' ? 'This is a proforma invoice without GST.' : ''));
        
        // Check if GST type changed from "without_gst" to "with_gst" - move to proforma_invoices table
        if ($previousGstType === 'without_gst' && $newGstType === 'with_gst') {
            return $this->moveToProformaInvoicesTable($invoice, $invoiceData, $status, $total);
        }
        
        $invoice->total_amount = $total;
        $invoice->invoice_data = $invoiceData;
        $invoice->save();
        
        // Log activity
        $this->logVendorActivity(
            $vendor->id,
            'updated',
            "Updated without GST invoice #{$invoice->invoice_number}: ₹" . number_format($total, 2),
            $invoice
        );
        
        return redirect()->back()->with('success', 'Without GST invoice updated successfully.');
    }
    
    /**
     * Move a without-GST invoice to the proforma_invoices table.
     *
     * @param  \App\Models\WithoutGstInvoice  $withoutGstInvoice
     * @param  array  $invoiceData
     * @param  string  $status
     * @param  float  $total
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function moveToProformaInvoicesTable(WithoutGstInvoice $withoutGstInvoice, array $invoiceData, string $status, float $total)
    {
        // Use database transaction to ensure data integrity
        return DB::transaction(function () use ($withoutGstInvoice, $invoiceData, $status, $total) {
            // Store old invoice number for notification
            $oldInvoiceNumber = $withoutGstInvoice->invoice_number;
            
            // Generate new invoice number for proforma invoice
            $invoiceNumber = $this->generateProformaInvoiceNumber();
            
            // Create new record in proforma_invoices table
            $proformaInvoice = ProformaInvoice::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $withoutGstInvoice->user_id,
                'vendor_id' => $withoutGstInvoice->vendor_id,
                'session_id' => $withoutGstInvoice->session_id,
                'total_amount' => $total,
                'paid_amount' => $withoutGstInvoice->paid_amount ?? 0,
                'payment_status' => $withoutGstInvoice->payment_status ?? 'unpaid',
                'invoice_data' => $invoiceData,
                'status' => $status,
            ]);
            
            // Create notification for the user (if user exists)
            if ($withoutGstInvoice->user_id) {
                Notification::create([
                    'user_id' => $withoutGstInvoice->user_id,
                    'title' => 'Invoice Converted to With GST',
                    'message' => "Your invoice #{$oldInvoiceNumber} has been converted to With GST invoice #{$proformaInvoice->invoice_number}",
                    'type' => 'invoice_converted',
                    'data' => json_encode([
                        'old_invoice_number' => $oldInvoiceNumber,
                        'new_invoice_number' => $proformaInvoice->invoice_number,
                        'invoice_id' => $proformaInvoice->id,
                        'invoice_type' => 'with_gst',
                    ]),
                    'read' => false,
                ]);
            }
            
            // Delete the original without-GST invoice
            $withoutGstInvoice->delete();
            
            // Redirect to the new proforma invoice in vendor panel
            return redirect()->route('vendor.invoices.show', $proformaInvoice->id)
                ->with('success', "Invoice converted to With GST successfully. New invoice number: {$proformaInvoice->invoice_number}");
        });
    }
    
    /**
     * Generate a unique invoice number for proforma invoices.
     *
     * @return string
     */
    private function generateProformaInvoiceNumber()
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        // Lock the table for reading to prevent race conditions
        $latestInvoice = ProformaInvoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->lockForUpdate()
            ->first();
        
        if ($latestInvoice) {
            $latestNumber = $latestInvoice->invoice_number;
            $parts = explode('-', $latestNumber);
            
            if (count($parts) >= 3 && $parts[1] == $year) {
                $sequence = (int)$parts[2] + 1;
            } else {
                $sequence = 1;
            }
        } else {
            $sequence = 1;
        }
        
        $sequenceFormatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "INV-{$year}-{$sequenceFormatted}";
    }
    
    /**
     * Update the status of the without GST invoice.
     */
    public function updateStatus(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:' . implode(',', WithoutGstInvoice::STATUS_OPTIONS)
        ]);
        
        $oldStatus = $invoice->status;
        $invoice->status = $request->input('status');
        $invoice->save();
        
        // Log activity
        $this->logVendorActivity(
            $vendor->id,
            'status_changed',
            "Changed without GST invoice #{$invoice->invoice_number} status: {$oldStatus} → {$invoice->status}",
            $invoice
        );
        
        return redirect()->back()->with('success', "Without GST invoice status updated to {$invoice->status} successfully.");
    }
    
    /**
     * Remove an item from the without GST invoice.
     * When an item is removed, the entire invoice is deleted.
     */
    public function removeItem(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        // Store invoice number for success message
        $invoiceNumber = $invoice->invoice_number;
        
        // Delete the entire invoice when removing an item
        $invoice->delete();
        
        return redirect()->route('vendor.invoices-black.index')
            ->with('success', "Invoice #{$invoiceNumber} has been deleted.");
    }
    
    /**
     * Delete the without GST invoice.
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        // Store info for logging before deletion
        $invoiceNumber = $invoice->invoice_number;
        $totalAmount = $invoice->total_amount;
        
        $invoice->delete();
        
        // Log activity
        $this->logVendorActivity(
            $vendor->id,
            'deleted',
            "Deleted without GST invoice #{$invoiceNumber}: ₹" . number_format($totalAmount, 2)
        );
        
        return redirect()->route('vendor.invoices-black.index')->with('success', 'Without GST invoice deleted successfully.');
    }
    
    /**
     * Download the invoice as PDF.
     */
    public function downloadPDF($id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $invoiceData = $invoice->invoice_data;
        
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? 0;
        $invoiceDate = $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;
        $invoiceNumber = $invoice->invoice_number;
        
        $pdf = Pdf::loadView('vendor.invoices-black.pdf', compact(
            'invoice',
            'cartItems',
            'total',
            'invoiceNumber',
            'invoiceDate',
            'customer',
            'invoiceData',
            'vendor'
        ));
        
        return $pdf->download('without-gst-invoice-' . $invoice->invoice_number . '.pdf');
    }
    
    /**
     * Add payment to the invoice.
     */
    public function addPayment(Request $request, $id)
    {
        $vendor = $this->getVendor();
        
        $invoice = WithoutGstInvoice::where('vendor_id', $vendor->id)->findOrFail($id);
        
        $pendingAmount = $invoice->total_amount - $invoice->paid_amount;
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $pendingAmount,
        ]);

        $newPaidAmount = $invoice->paid_amount + $request->amount;
        
        // Determine new payment status
        if ($newPaidAmount >= $invoice->total_amount) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
        ]);
        
        // Log activity
        $this->logVendorActivity(
            $vendor->id,
            'payment_added',
            "Added payment of ₹" . number_format($request->amount, 2) . " to without GST invoice #{$invoice->invoice_number} - Status: {$paymentStatus}",
            $invoice
        );

        return redirect()->back()->with('success', 'Payment of ₹' . number_format($request->amount, 2) . ' added successfully.');
    }
}