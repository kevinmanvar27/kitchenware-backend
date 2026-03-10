<?php

namespace App\Http\Controllers\API;

use App\Models\ProformaInvoice;
use App\Models\ShoppingCartItem;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * @OA\Tag(
 *     name="My Invoices",
 *     description="API Endpoints for User's Proforma Invoices"
 * )
 */
class MyInvoiceController extends ApiController
{
    /**
     * Decode invoice data - handles both array (from model cast) and JSON string
     *
     * @param mixed $invoiceData
     * @return array|null
     */
    private function decodeInvoiceData($invoiceData): ?array
    {
        if (is_array($invoiceData)) {
            return $invoiceData;
        }
        
        if (is_string($invoiceData)) {
            return json_decode($invoiceData, true);
        }
        
        return null;
    }

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
                    'store_name' => $vendor->store_name ?? $vendor->business_name ?? (function_exists('setting') ? setting('site_title', 'Store') : 'Store'),
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
     * Get authenticated user's proforma invoices
     * 
     * @OA\Get(
     *      path="/api/v1/my-invoices",
     *      operationId="getMyInvoices",
     *      tags={"My Invoices"},
     *      summary="Get user's invoices",
     *      description="Returns the authenticated user's proforma invoices",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Filter by status (draft, sent, paid, cancelled)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = ProformaInvoice::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $invoices = $query->paginate(15);
        
        // Decode invoice_data for each invoice
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->invoice_data_decoded = $this->decodeInvoiceData($invoice->invoice_data);
            return $invoice;
        });
        
        return $this->sendResponse($invoices, 'Invoices retrieved successfully.');
    }

    /**
     * Get specific invoice details
     * 
     * @OA\Get(
     *      path="/api/v1/my-invoices/{id}",
     *      operationId="getMyInvoiceById",
     *      tags={"My Invoices"},
     *      summary="Get invoice details",
     *      description="Returns the details of a specific proforma invoice",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$invoice) {
            return $this->sendError('Invoice not found.', [], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);

        return $this->sendResponse([
            'invoice' => $invoice,
            'data' => $invoiceData,
        ], 'Invoice retrieved successfully.');
    }

    /**
     * Download invoice PDF
     * 
     * @OA\Get(
     *      path="/api/v1/my-invoices/{id}/download-pdf",
     *      operationId="downloadMyInvoicePdf",
     *      tags={"My Invoices"},
     *      summary="Download invoice PDF",
     *      description="Download the proforma invoice as PDF",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="PDF file",
     *          @OA\MediaType(
     *              mediaType="application/pdf"
     *          )
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Request $request, $id)
    {
        $user = $request->user();
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$invoice) {
            return $this->sendError('Invoice not found.', [], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        
        // Get store details (vendor or default)
        $store = $this->getStoreDetails($invoice);

        // Prepare data for the PDF view
        $data = [
            'invoice' => $invoice,
            'invoiceData' => $invoiceData,
            'siteTitle' => function_exists('setting') ? setting('site_title', 'Frontend App') : 'Frontend App',
            'companyAddress' => function_exists('setting') ? setting('address', 'Company Address') : 'Company Address',
            'companyEmail' => function_exists('setting') ? setting('email', 'company@example.com') : 'company@example.com',
            'companyPhone' => function_exists('setting') ? setting('phone', '+1 (555) 123-4567') : '+1 (555) 123-4567',
            'store' => $store,
        ];

        // Load the PDF view
        $pdf = Pdf::loadView('frontend.proforma-invoice-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        // If invoice is approved, name it as 'invoice' instead of 'proforma-invoice'
        $filePrefix = ($invoice->status === \App\Models\ProformaInvoice::STATUS_APPROVED) 
            ? 'invoice' 
            : 'proforma-invoice';
        return $pdf->download($filePrefix . '-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Add invoice items to cart
     * 
     * NOTE: Stock was already reduced when the invoice was created.
     * When adding invoice items back to cart, we don't reduce stock again.
     * The items are simply moving from invoice to cart.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request, $id)
    {
        $user = $request->user();
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$invoice) {
            return $this->sendError('Invoice not found.', [], 404);
        }

        // Check if invoice is in draft status
        $draftStatus = defined('App\Models\ProformaInvoice::STATUS_DRAFT') 
            ? ProformaInvoice::STATUS_DRAFT 
            : 'draft';
            
        if ($invoice->status !== $draftStatus) {
            return $this->sendError('Only draft invoices can be added to cart.', [], 400);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $addedItems = [];
        $skippedItems = [];

        // NOTE: Stock was already reduced when invoice was created
        // We're just moving items from invoice to cart, no stock change needed
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                // Check if product still exists
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    $skippedItems[] = $item['product_name'] ?? 'Unknown Product';
                    continue;
                }

                // Check if item already exists in cart
                $existingCartItem = ShoppingCartItem::where('user_id', $user->id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($existingCartItem) {
                    // Item already in cart - merge quantities
                    // Stock was already reduced for invoice items, so just add quantity
                    $newQuantity = $existingCartItem->quantity + $item['quantity'];
                    $existingCartItem->update([
                        'quantity' => $newQuantity,
                        'price' => $item['price']
                    ]);
                } else {
                    // Item not in cart - create new cart item
                    // Stock was already reduced when invoice was created, no need to reduce again
                    ShoppingCartItem::create([
                        'user_id' => $user->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
                
                $addedItems[] = $item['product_name'];
            }
        }

        // Delete the invoice after adding items to cart
        // NOTE: We don't restore stock here because items are moving to cart (stock stays reduced)
        $invoice->delete();

        $message = 'Products from invoice added to cart successfully.';
        if (!empty($skippedItems)) {
            $message .= ' Some items were skipped (product no longer available): ' . implode(', ', $skippedItems);
        }

        return $this->sendResponse([
            'added_items' => $addedItems,
            'skipped_items' => $skippedItems,
            'cart_count' => ShoppingCartItem::where('user_id', $user->id)->count(),
        ], $message);
    }

    /**
     * Delete a proforma invoice
     * 
     * @OA\Delete(
     *      path="/api/v1/my-invoices/{id}",
     *      operationId="deleteMyInvoice",
     *      tags={"My Invoices"},
     *      summary="Delete invoice",
     *      description="Delete a proforma invoice",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$invoice) {
            return $this->sendError('Invoice not found.', [], 404);
        }

        // RESTORE STOCK for all items in the invoice before deleting
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        
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

        $invoice->delete();

        return $this->sendResponse(null, 'Invoice deleted and stock restored successfully.');
    }

    /**
     * Remove a specific item from user's proforma invoice.
     * If the invoice becomes empty after removal, it will be automatically deleted.
     * 
     * @OA\Delete(
     *      path="/api/v1/my-invoices/{id}/items/{productId}",
     *      operationId="removeMyInvoiceItem",
     *      tags={"My Invoices"},
     *      summary="Remove item from invoice",
     *      description="Removes a specific item from the user's proforma invoice and restores stock. If the invoice becomes empty, it will be automatically deleted.",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="productId",
     *          description="Product ID to remove from invoice",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(Request $request, $id, $productId)
    {
        $user = $request->user();
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$invoice) {
            return $this->sendError('Invoice not found.', [], 404);
        }

        // Decode invoice data
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);

        if (!isset($invoiceData['cart_items']) || !is_array($invoiceData['cart_items'])) {
            return $this->sendError('Invoice has no items.');
        }

        // Find and remove the item
        $itemFound = false;
        $removedItem = null;
        $newCartItems = [];

        foreach ($invoiceData['cart_items'] as $item) {
            if (($item['product_id'] ?? null) == $productId) {
                $itemFound = true;
                $removedItem = $item;
                
                // RESTORE STOCK for the removed item
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
                    $product = Product::find($productId);
                    if ($product) {
                        $product->increment('stock_quantity', $item['quantity']);
                        
                        // Update in_stock status if stock was restored
                        if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                            $product->update(['in_stock' => true]);
                        }
                    }
                }
            } else {
                $newCartItems[] = $item;
            }
        }

        if (!$itemFound) {
            return $this->sendError('Item not found in invoice.', [], 404);
        }

        // Check if invoice is now empty - auto-delete if so
        if (empty($newCartItems)) {
            $invoice->delete();
            return $this->sendResponse([
                'invoice_deleted' => true,
                'removed_item' => $removedItem,
            ], 'Last item removed. Invoice has been deleted.');
        }

        // Update invoice data with remaining items
        $invoiceData['cart_items'] = $newCartItems;
        
        // Recalculate total
        $newTotal = array_reduce($newCartItems, function ($carry, $item) {
            return $carry + (($item['price'] ?? 0) * ($item['quantity'] ?? 0));
        }, 0);
        
        $invoiceData['total'] = $newTotal;

        // Update the invoice
        $invoice->update([
            'invoice_data' => $invoiceData,
            'total_amount' => $newTotal,
        ]);

        return $this->sendResponse([
            'invoice' => $invoice->fresh(),
            'removed_item' => $removedItem,
            'invoice_deleted' => false,
        ], 'Item removed from invoice and stock restored successfully.');
    }
}
