<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithoutGstInvoice;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\LogsActivity;

class WithoutGstInvoiceController extends Controller
{
    use LogsActivity;
    
    /**
     * Get store details for invoice - vendor store or default site
     *
     * @param WithoutGstInvoice $invoice
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
     * Display a listing of without GST invoices.
     */
    public function index()
    {
        $withoutGstInvoices = WithoutGstInvoice::with(['user', 'vendor'])->orderBy('created_at', 'desc')->get();
        
        return view('admin.without-gst-invoice.index', compact('withoutGstInvoices'));
    }
    
    /**
     * Display the specified without GST invoice.
     */
    public function show($id)
    {
        $invoice = WithoutGstInvoice::with(['user', 'vendor'])->findOrFail($id);
        
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
        
        // Get store details (vendor or default)
        $store = $this->getStoreDetails($invoice);
        
        // Remove notifications for this invoice
        if (Auth::check()) {
            Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->where('type', 'without_gst_invoice')
                ->where('data', 'like', '%"invoice_id":' . $id . '%')
                ->delete();
        }
        
        return view('admin.without-gst-invoice.show', compact('invoice', 'cartItems', 'total', 'invoiceNumber', 'invoiceDate', 'customer', 'invoiceData', 'store'));
    }
    
    /**
     * Update the without GST invoice.
     */
    public function update(Request $request, $id)
    {
        $invoice = WithoutGstInvoice::findOrFail($id);
        
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
        if ($request->has('status')) {
            $request->validate([
                'status' => 'required|in:' . implode(',', WithoutGstInvoice::STATUS_OPTIONS)
            ]);
            $invoice->status = $request->input('status');
        }
        
        // Update cart items if provided
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
            }
            
            $invoiceData['cart_items'] = $cartItems;
        }
        
        // Update invoice details (without GST - tax is always 0)
        $invoiceData['subtotal'] = (float) $request->input('subtotal', $invoiceData['subtotal'] ?? 0);
        $invoiceData['discount_percentage'] = (float) $request->input('discount_percentage', $invoiceData['discount_percentage'] ?? 0);
        $invoiceData['discount_amount'] = (float) $request->input('discount_amount', $invoiceData['discount_amount'] ?? 0);
        $invoiceData['shipping'] = (float) $request->input('shipping', $invoiceData['shipping'] ?? 0);
        $invoiceData['gst_type'] = 'without_gst';
        $invoiceData['tax_percentage'] = 0;
        $invoiceData['tax_amount'] = 0;
        $invoiceData['total'] = (float) $request->input('total', $invoiceData['total'] ?? 0);
        $invoiceData['notes'] = $request->input('notes', $invoiceData['notes'] ?? 'This is a proforma invoice without GST.');
        
        $invoice->total_amount = (float) $request->input('total', $invoice->total_amount);
        $invoice->invoice_data = $invoiceData;
        $invoice->save();
        
        // Log activity
        $this->logAdminActivity('updated', "Updated without GST invoice: #{$invoice->invoice_number}", $invoice);
        
        return redirect()->back()->with('success', 'Without GST invoice updated successfully.');
    }
    
    /**
     * Update the status of the without GST invoice.
     */
    public function updateStatus(Request $request, $id)
    {
        $invoice = WithoutGstInvoice::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:' . implode(',', WithoutGstInvoice::STATUS_OPTIONS)
        ]);
        
        $oldStatus = $invoice->status;
        $invoice->status = $request->input('status');
        $invoice->save();
        
        // Log activity
        $this->logAdminActivity('status_changed', "Changed without GST invoice #{$invoice->invoice_number} status: {$oldStatus} → {$invoice->status}", $invoice);
        
        return redirect()->back()->with('success', "Without GST invoice status updated to {$invoice->status} successfully.");
    }
    
    /**
     * Remove an item from the without GST invoice.
     */
    public function removeItem(Request $request, $id)
    {
        $invoice = WithoutGstInvoice::findOrFail($id);
        
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
        
        $itemIndex = $request->input('item_index');
        
        if (!isset($invoiceData['cart_items'][$itemIndex])) {
            return redirect()->back()->with('error', 'Invalid item selection.');
        }
        
        // RESTORE STOCK for the removed item
        $removedItem = $invoiceData['cart_items'][$itemIndex];
        
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
        $invoiceData['cart_items'] = array_values($invoiceData['cart_items']);
        
        // If no items left, delete the invoice
        if (empty($invoiceData['cart_items'])) {
            $invoice->delete();
            return redirect()->route('admin.without-gst-invoice.index')->with('success', 'Last item removed. Invoice has been deleted.');
        }
        
        $newTotal = 0;
        foreach ($invoiceData['cart_items'] as $item) {
            $newTotal += $item['total'];
        }
        
        $invoiceData['total'] = $newTotal;
        
        $invoice->total_amount = $newTotal;
        $invoice->invoice_data = $invoiceData;
        $invoice->save();
        
        return redirect()->back()->with('success', 'Item removed successfully. Total updated.');
    }
    
    /**
     * Generate and download PDF for a without GST invoice.
     */
    public function downloadPDF($id)
    {
        $invoice = WithoutGstInvoice::with(['user', 'vendor'])->findOrFail($id);
        
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
        
        $settings = Setting::first();
        
        // Get store details (vendor or default)
        $store = $this->getStoreDetails($invoice);
        
        $data = [
            'invoice' => $invoice,
            'proformaInvoice' => $invoice,
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
            'isWithoutGst' => true,
            'store' => $store,
        ];
        
        $pdf = Pdf::loadView('admin.without-gst-invoice-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('without-gst-invoice-' . $invoice->invoice_number . '.pdf');
    }
    
    /**
     * Remove the specified without GST invoice from storage.
     */
    public function destroy($id)
    {
        $invoice = WithoutGstInvoice::findOrFail($id);
        
        // RESTORE STOCK for all items in the invoice before deleting
        $invoiceData = $invoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }
        
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
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
        
        // Capture data before deletion for logging
        $invoiceNumber = $invoice->invoice_number;
        $invoiceId = $invoice->id;
        
        $invoice->delete();
        
        // Log activity
        $this->logAdminActivity('deleted', "Deleted without GST invoice: #{$invoiceNumber} (ID: {$invoiceId}) and restored stock");
        
        return redirect()->route('admin.without-gst-invoice.index')->with('success', 'Without GST invoice deleted and stock restored successfully.');
    }
}
