<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProformaInvoice;
use App\Models\WithoutGstInvoice;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\LogsActivity;

class ProformaInvoiceController extends Controller
{
    use LogsActivity;
    /**
     * Get store details for invoice - vendor store or default site
     *
     * @param ProformaInvoice $invoice
     * @return array|null
     */
    private function getStoreDetails($invoice)
    {
        // Check if invoice has a vendor_id
        if ($invoice->vendor_id) {
            $vendor = Vendor::find($invoice->vendor_id);
            if ($vendor) {
                return [
                    'store_name' => $vendor->store_name ?? $vendor->business_name ?? setting('site_title', 'Store'),
                    'store_logo' => $vendor->store_logo,
                    'business_email' => $vendor->business_email,
                    'business_phone' => $vendor->business_phone,
                    'business_address' => $vendor->business_address,
                    'full_address' => $this->buildFullAddress($vendor),
                    'city' => $vendor->city,
                    'state' => $vendor->state,
                    'postal_code' => $vendor->postal_code,
                    'gst_number' => $vendor->gst_number,
                    'vendor_id' => $vendor->id,
                ];
            }
        }
        
        // Return null to use default site details
        return null;
    }

    /**
     * Build full address from vendor details
     */
    private function buildFullAddress($vendor)
    {
        $parts = array_filter([
            $vendor->business_address,
            $vendor->city,
            $vendor->state,
            $vendor->postal_code
        ]);
        return implode(', ', $parts);
    }

    /**
     * Display a listing of proforma invoices.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Start query with relationships
        $query = ProformaInvoice::with(['user', 'vendor']);
        
        // Apply filters
        if ($request->filled('client')) {
            $query->where('user_id', $request->client);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get filtered invoices
        $proformaInvoices = $query->orderBy('created_at', 'desc')->get();
        
        // Calculate summary statistics
        $totalAmount = $proformaInvoices->sum('total_amount');
        $totalInvoices = $proformaInvoices->count();
        
        // Count by status
        $statusCounts = [
            'Draft' => $proformaInvoices->where('status', 'Draft')->count(),
            'Approved' => $proformaInvoices->where('status', 'Approved')->count(),
            'Dispatch' => $proformaInvoices->where('status', 'Dispatch')->count(),
            'Out for Delivery' => $proformaInvoices->where('status', 'Out for Delivery')->count(),
            'Delivered' => $proformaInvoices->where('status', 'Delivered')->count(),
            'Return' => $proformaInvoices->where('status', 'Return')->count(),
        ];
        
        // Get all users who have proforma invoices for filter dropdown
        $clients = \App\Models\User::whereHas('proformaInvoices')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return view('admin.proforma-invoice.index', compact(
            'proformaInvoices',
            'totalAmount',
            'totalInvoices',
            'statusCounts',
            'clients'
        ));
    }
    
    /**
     * Display the specified proforma invoice.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $proformaInvoice = ProformaInvoice::with(['user', 'vendor'])->findOrFail($id);
        
        // Get invoice data (handle both array and JSON string, including double-encoded)
        $invoiceData = $proformaInvoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Extract cart items and customer info
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? 0;
        $invoiceDate = $invoiceData['invoice_date'] ?? $proformaInvoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;
        
        // Generate invoice number (for display consistency)
        $invoiceNumber = $proformaInvoice->invoice_number;
        
        // Get store details (vendor or default)
        $store = $this->getStoreDetails($proformaInvoice);
        
        // Automatically remove all notifications for this invoice when viewing directly
        if (Auth::check()) {
            // Get all unread notifications for the current user that are related to this invoice
            $notifications = Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->where('type', 'proforma_invoice')
                ->where('data', 'like', '%"invoice_id":' . $id . '%')
                ->get();
            
            // Delete all matching notifications
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }
        
        return view('admin.proforma-invoice.show', compact('proformaInvoice', 'cartItems', 'total', 'invoiceNumber', 'invoiceDate', 'customer', 'invoiceData', 'store'));
    }
    
    /**
     * Update the proforma invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $proformaInvoice = ProformaInvoice::findOrFail($id);
        
        // Get the existing invoice data (handle both array and JSON string for backward compatibility)
        $invoiceData = $proformaInvoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded from old records)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Update status if provided
        if ($request->has('status')) {
            // Validate the status input
            $request->validate([
                'status' => 'required|in:' . implode(',', ProformaInvoice::STATUS_OPTIONS)
            ]);
            
            // Update the status
            $proformaInvoice->status = $request->input('status');
        }
        
        // Update cart items if provided
        if ($request->has('items')) {
            $items = $request->input('items');
            $cartItems = [];
            
            foreach ($items as $index => $item) {
                // Get the original item data
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
            }
            
            $invoiceData['cart_items'] = $cartItems;
        }
        
        // Update invoice details
        $invoiceData['subtotal'] = (float) $request->input('subtotal', $invoiceData['subtotal'] ?? 0);
        $invoiceData['discount_percentage'] = (float) $request->input('discount_percentage', $invoiceData['discount_percentage'] ?? 0);
        $invoiceData['discount_amount'] = (float) $request->input('discount_amount', $invoiceData['discount_amount'] ?? 0);
        $invoiceData['shipping'] = (float) $request->input('shipping', $invoiceData['shipping'] ?? 0);
        
        // Handle GST type
        $previousGstType = $invoiceData['gst_type'] ?? 'with_gst';
        $newGstType = $request->input('gst_type', $previousGstType);
        $invoiceData['gst_type'] = $newGstType;
        
        // If without GST, force tax values to 0
        if ($newGstType === 'without_gst') {
            $invoiceData['tax_percentage'] = 0;
            $invoiceData['tax_amount'] = 0;
        } else {
            $invoiceData['tax_percentage'] = (float) $request->input('tax_percentage', $invoiceData['tax_percentage'] ?? 18);
            $invoiceData['tax_amount'] = (float) $request->input('tax_amount', $invoiceData['tax_amount'] ?? 0);
        }
        
        $invoiceData['total'] = (float) $request->input('total', $invoiceData['total'] ?? 0);
        $invoiceData['notes'] = $request->input('notes', $invoiceData['notes'] ?? 'This is a proforma invoice and not a tax invoice. Payment is due upon receipt.');
        
        // Check if GST type changed from "with_gst" to "without_gst" - move to separate table
        if ($previousGstType !== 'without_gst' && $newGstType === 'without_gst') {
            return $this->moveToWithoutGstTable($proformaInvoice, $invoiceData, $request);
        }
        
        // Update the proforma invoice
        $proformaInvoice->total_amount = (float) $request->input('total', $proformaInvoice->total_amount);
        $proformaInvoice->invoice_data = $invoiceData;
        $proformaInvoice->save();
        
        // Log the activity
        $this->logAdminActivity('updated', "Updated proforma invoice: {$proformaInvoice->invoice_number}", $proformaInvoice);
        
        return redirect()->back()->with('success', 'Proforma invoice updated successfully.');
    }
    
    /**
     * Move a proforma invoice to the without_gst_invoices table.
     *
     * @param  \App\Models\ProformaInvoice  $proformaInvoice
     * @param  array  $invoiceData
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function moveToWithoutGstTable(ProformaInvoice $proformaInvoice, array $invoiceData, Request $request)
    {
        // Use database transaction to ensure data integrity
        return DB::transaction(function () use ($proformaInvoice, $invoiceData, $request) {
            // Store old invoice number for notification
            $oldInvoiceNumber = $proformaInvoice->invoice_number;
            $originalInvoiceId = $proformaInvoice->id;
            
            // Update status if provided
            $status = $request->input('status', $proformaInvoice->status);
            
            // Create new record in without_gst_invoices table
            $withoutGstInvoice = WithoutGstInvoice::create([
                'user_id' => $proformaInvoice->user_id,
                'vendor_id' => $proformaInvoice->vendor_id,
                'session_id' => $proformaInvoice->session_id,
                'total_amount' => (float) $request->input('total', $proformaInvoice->total_amount),
                'paid_amount' => $proformaInvoice->paid_amount ?? 0,
                'payment_status' => $proformaInvoice->payment_status ?? 'unpaid',
                'invoice_data' => $invoiceData,
                'status' => $status,
                'original_invoice_id' => $originalInvoiceId,
            ]);
            
            // Create notification for the user (if user exists)
            if ($proformaInvoice->user_id) {
                Notification::create([
                    'user_id' => $proformaInvoice->user_id,
                    'title' => 'Invoice Converted to Without GST',
                    'message' => "Your invoice #{$oldInvoiceNumber} has been converted to Without GST invoice #{$withoutGstInvoice->invoice_number}",
                    'type' => 'invoice_converted',
                    'data' => json_encode([
                        'old_invoice_number' => $oldInvoiceNumber,
                        'new_invoice_number' => $withoutGstInvoice->invoice_number,
                        'invoice_id' => $withoutGstInvoice->id,
                        'invoice_type' => 'without_gst',
                    ]),
                    'read' => false,
                ]);
            }
            
            // Create notification for the vendor (if vendor exists)
            if ($proformaInvoice->vendor_id) {
                $vendor = Vendor::find($proformaInvoice->vendor_id);
                if ($vendor && $vendor->user_id) {
                    Notification::create([
                        'user_id' => $vendor->user_id,
                        'title' => 'Invoice Converted to Without GST',
                        'message' => "Invoice #{$oldInvoiceNumber} has been converted to Without GST invoice #{$withoutGstInvoice->invoice_number}",
                        'type' => 'without_gst_invoice',
                        'data' => json_encode([
                            'old_invoice_number' => $oldInvoiceNumber,
                            'new_invoice_number' => $withoutGstInvoice->invoice_number,
                            'invoice_id' => $withoutGstInvoice->id,
                            'invoice_type' => 'without_gst',
                        ]),
                        'read' => false,
                    ]);
                }
            }
            
            // Delete the original proforma invoice
            $proformaInvoice->delete();
            
            // Redirect to the new without-GST invoice
            return redirect()->route('admin.without-gst-invoice.show', $withoutGstInvoice->id)
                ->with('success', "Invoice converted to Without GST successfully. New invoice number: {$withoutGstInvoice->invoice_number}");
        });
    }
    
    /**
     * Update the status of the proforma invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $proformaInvoice = ProformaInvoice::findOrFail($id);
        
        // Validate the status input
        $request->validate([
            'status' => 'required|in:' . implode(',', ProformaInvoice::STATUS_OPTIONS)
        ]);
        
        $oldStatus = $proformaInvoice->status;
        
        // Update the status
        $proformaInvoice->status = $request->input('status');
        $proformaInvoice->save();
        
        // Log the activity
        $this->logAdminActivity('status_changed', "Changed proforma invoice {$proformaInvoice->invoice_number} status: {$oldStatus} → {$proformaInvoice->status}", $proformaInvoice);
        
        return redirect()->back()->with('success', "Proforma invoice status updated to {$proformaInvoice->status} successfully.");
    }
    
    /**
     * Remove an item from the proforma invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeItem(Request $request, $id)
    {
        $proformaInvoice = ProformaInvoice::findOrFail($id);
        
        // Get invoice data (handle both array and JSON string for backward compatibility)
        $invoiceData = $proformaInvoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded from old records)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Get the item index to remove
        $itemIndex = $request->input('item_index');
        
        // Validate item index
        if (!isset($invoiceData['cart_items'][$itemIndex])) {
            return redirect()->back()->with('error', 'Invalid item selection.');
        }
        
        // Remove the item and restore stock
        $removedItem = $invoiceData['cart_items'][$itemIndex];
        
        // RESTORE STOCK for the removed item
        // Check if this is a variable product with variation
        if (!empty($removedItem['product_variation_id'])) {
            // Restore variation stock
            $variation = \App\Models\ProductVariation::find($removedItem['product_variation_id']);
            if ($variation) {
                $variation->increment('stock_quantity', $removedItem['quantity']);
                
                // Update variation in_stock status if stock was restored
                if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                    $variation->update(['in_stock' => true]);
                }
            }
        } else {
            // Restore simple product stock
            $product = Product::find($removedItem['product_id'] ?? null);
            if ($product) {
                $product->increment('stock_quantity', $removedItem['quantity']);
                
                // Update in_stock status if stock was restored
                if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                    $product->update(['in_stock' => true]);
                }
            }
        }
        
        unset($invoiceData['cart_items'][$itemIndex]);
        
        // Re-index array to ensure sequential keys
        $invoiceData['cart_items'] = array_values($invoiceData['cart_items']);
        
        // If no items left, delete the invoice
        if (empty($invoiceData['cart_items'])) {
            $proformaInvoice->delete();
            return redirect()->route('admin.proforma-invoice.index')->with('success', 'Last item removed. Invoice has been deleted.');
        }
        
        // Recalculate total
        $newTotal = 0;
        foreach ($invoiceData['cart_items'] as $item) {
            $newTotal += $item['total'];
        }
        
        $invoiceData['total'] = $newTotal;
        
        // Update the proforma invoice
        $proformaInvoice->total_amount = $newTotal;
        $proformaInvoice->invoice_data = $invoiceData;
        $proformaInvoice->save();
        
        return redirect()->back()->with('success', 'Item removed successfully. Total updated.');
    }
    
    /**
     * Generate and download PDF for a proforma invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadPDF($id)
    {
        $proformaInvoice = ProformaInvoice::with(['user', 'vendor'])->findOrFail($id);
        
        // Get invoice data (handle both array and JSON string, including double-encoded)
        $invoiceData = $proformaInvoice->invoice_data;
        
        // Handle case where invoice_data might be a JSON string (double-encoded)
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            // Check if it's still a string (triple-encoded edge case)
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        // Ensure we have an array
        if (!is_array($invoiceData)) {
            $invoiceData = [];
        }
        
        // Extract cart items and customer info
        $cartItems = $invoiceData['cart_items'] ?? [];
        $total = $invoiceData['total'] ?? 0;
        $invoiceDate = $invoiceData['invoice_date'] ?? $proformaInvoice->created_at->format('Y-m-d');
        $customer = $invoiceData['customer'] ?? null;
        
        // Generate invoice number (for display consistency)
        $invoiceNumber = $proformaInvoice->invoice_number;
        
        // Get settings
        $settings = Setting::first();
        
        // Get store details (vendor or default)
        $store = $this->getStoreDetails($proformaInvoice);
        
        // Prepare data for the PDF view
        $data = [
            'proformaInvoice' => $proformaInvoice,
            'cartItems' => $cartItems,
            'total' => $total,
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $invoiceDate,
            'customer' => $customer,
            'invoiceData' => $invoiceData,
            'siteTitle' => setting('site_title', 'Admin Panel'),
            'companyAddress' => setting('address', 'Company Address'),
            'companyEmail' => setting('email', 'company@example.com'),
            'companyPhone' => setting('phone', '+1 (555) 123-4567'),
            'headerLogo' => setting('header_logo', null),
            'settings' => $settings,
            'store' => $store,
        ];
        
        // Load the PDF view
        $pdf = Pdf::loadView('admin.proforma-invoice-pdf', $data);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Download the PDF with a meaningful filename
        // If invoice is approved, name it as 'invoice' instead of 'proforma-invoice'
        $filePrefix = ($proformaInvoice->status === ProformaInvoice::STATUS_APPROVED) 
            ? 'invoice' 
            : 'proforma-invoice';
        return $pdf->download($filePrefix . '-' . $proformaInvoice->invoice_number . '.pdf');
    }
    
    /**
     * Remove the specified proforma invoice from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $proformaInvoice = ProformaInvoice::findOrFail($id);
        
        // RESTORE STOCK for all items in the invoice before deleting
        $invoiceData = $proformaInvoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                // Check if this is a variable product with variation
                if (!empty($item['product_variation_id'])) {
                    // Restore variation stock
                    $variation = \App\Models\ProductVariation::find($item['product_variation_id']);
                    if ($variation) {
                        $variation->increment('stock_quantity', $item['quantity']);
                        
                        // Update variation in_stock status if stock was restored
                        if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                            $variation->update(['in_stock' => true]);
                        }
                    }
                } else {
                    // Restore simple product stock
                    $product = Product::find($item['product_id'] ?? null);
                    if ($product) {
                        $product->increment('stock_quantity', $item['quantity']);
                        
                        // Update in_stock status if stock was restored
                        if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                            $product->update(['in_stock' => true]);
                        }
                    }
                }
            }
        }
        
        $invoiceNumber = $proformaInvoice->invoice_number;
        $invoiceId = $proformaInvoice->id;
        
        $proformaInvoice->delete();
        
        // Log the activity
        $this->logAdminActivity('deleted', "Deleted proforma invoice: {$invoiceNumber} (ID: {$invoiceId})");
        
        return redirect()->route('admin.proforma-invoice.index')->with('success', 'Proforma invoice deleted and stock restored successfully.');
    }
}