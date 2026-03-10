<?php

namespace App\Http\Controllers\API;

use App\Models\ProformaInvoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Proforma Invoices",
 *     description="API Endpoints for Proforma Invoice Management"
 * )
 */
class ProformaInvoiceController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/proforma-invoices",
     *      operationId="getProformaInvoicesList",
     *      tags={"Proforma Invoices"},
     *      summary="Get list of proforma invoices",
     *      description="Returns list of proforma invoices with pagination",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              default=15
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Filter by status",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"Draft", "Approved", "Dispatch", "Out for Delivery", "Delivered", "Return"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          description="Filter by user ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
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
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index(Request $request)
    {
        $query = ProformaInvoice::with('user');
        
        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ProformaInvoice::STATUS_OPTIONS)) {
            $query->where('status', $request->status);
        }
        
        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Search by invoice number
        if ($request->has('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }
        
        // Order by latest first
        $query->orderBy('created_at', 'desc');
        
        $perPage = $request->get('per_page', 15);
        $invoices = $query->paginate($perPage);
        
        return $this->sendResponse($invoices, 'Proforma invoices retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/proforma-invoices",
     *      operationId="storeProformaInvoice",
     *      tags={"Proforma Invoices"},
     *      summary="Store new proforma invoice",
     *      description="Returns proforma invoice data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"total_amount","invoice_data"},
     *              @OA\Property(property="user_id", type="integer", example=1, nullable=true),
     *              @OA\Property(property="session_id", type="string", example="abc123", nullable=true),
     *              @OA\Property(property="total_amount", type="number", format="float", example=199.99),
     *              @OA\Property(property="status", type="string", example="Draft", enum={"Draft", "Approved", "Dispatch", "Out for Delivery", "Delivered", "Return"}),
     *              @OA\Property(property="invoice_data", type="object", example={"cart_items": [], "subtotal": 100, "total": 199.99}),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'session_id' => 'nullable|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'nullable|in:' . implode(',', ProformaInvoice::STATUS_OPTIONS),
            'invoice_data' => 'required|array',
        ]);

        // Create proforma invoice with auto-generated invoice number
        $invoice = $this->createProformaInvoiceWithRetry($request);

        return $this->sendResponse($invoice->load('user', 'vendor'), 'Proforma invoice created successfully.', 201);
    }

    /**
     * Generate a serialized invoice number with database locking to prevent duplicates.
     *
     * @return string
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        // Use database locking to prevent race conditions
        return DB::transaction(function () use ($year, $prefix) {
            // Lock the table for reading to prevent concurrent reads
            $latestInvoice = ProformaInvoice::where('invoice_number', 'like', $prefix . '%')
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($latestInvoice) {
                $parts = explode('-', $latestInvoice->invoice_number);
                if (count($parts) >= 3 && $parts[1] == $year) {
                    $sequence = (int)$parts[2] + 1;
                } else {
                    $sequence = 1;
                }
            } else {
                $sequence = 1;
            }

            return "INV-{$year}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Create a proforma invoice with retry logic to handle duplicate invoice numbers.
     *
     * @param  Request  $request
     * @param  int  $maxRetries
     * @return \App\Models\ProformaInvoice
     * @throws \Exception
     */
    private function createProformaInvoiceWithRetry(Request $request, $maxRetries = 5)
    {
        $attempts = 0;
        $lastException = null;
        
        while ($attempts < $maxRetries) {
            try {
                return DB::transaction(function () use ($request) {
                    // Generate invoice number inside the transaction
                    $invoiceNumber = $this->generateInvoiceNumber();
                    
                    // Create the proforma invoice
                    return ProformaInvoice::create([
                        'invoice_number' => $invoiceNumber,
                        'user_id' => $request->get('user_id'),
                        'vendor_id' => $request->get('vendor_id'),
                        'session_id' => $request->get('session_id'),
                        'total_amount' => $request->get('total_amount'),
                        'invoice_data' => $request->get('invoice_data'),
                        'status' => $request->get('status', ProformaInvoice::STATUS_DRAFT),
                    ]);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                $lastException = $e;
                
                // Check if it's a duplicate entry error (MySQL error code 1062)
                if ($e->errorInfo[1] == 1062) {
                    $attempts++;
                    // Small delay before retry to reduce collision chance
                    usleep(100000 * $attempts); // 100ms * attempt number
                    continue;
                }
                
                // If it's not a duplicate entry error, rethrow
                throw $e;
            }
        }
        
        // If we've exhausted all retries, throw the last exception
        throw $lastException ?? new \Exception('Failed to create proforma invoice after ' . $maxRetries . ' attempts');
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/proforma-invoices/{id}",
     *      operationId="getProformaInvoiceById",
     *      tags={"Proforma Invoices"},
     *      summary="Get proforma invoice information",
     *      description="Returns proforma invoice data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Proforma invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function show($id)
    {
        $invoice = ProformaInvoice::with('user')->find($id);

        if (is_null($invoice)) {
            return $this->sendError('Proforma invoice not found.');
        }

        return $this->sendResponse($invoice, 'Proforma invoice retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/proforma-invoices/{id}",
     *      operationId="updateProformaInvoice",
     *      tags={"Proforma Invoices"},
     *      summary="Update existing proforma invoice",
     *      description="Returns updated proforma invoice data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Proforma invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="integer", example=1, nullable=true),
     *              @OA\Property(property="invoice_number", type="string", example="PI-2025-001"),
     *              @OA\Property(property="total_amount", type="number", format="float", example=199.99),
     *              @OA\Property(property="status", type="string", example="Approved", enum={"Draft", "Approved", "Dispatch", "Out for Delivery", "Delivered", "Return"}),
     *              @OA\Property(property="invoice_data", type="object", example={"cart_items": [], "subtotal": 100, "total": 199.99}),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $invoice = ProformaInvoice::find($id);

        if (is_null($invoice)) {
            return $this->sendError('Proforma invoice not found.');
        }

        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'session_id' => 'nullable|string|max:255',
            'invoice_number' => 'sometimes|string|max:255|unique:proforma_invoices,invoice_number,' . $id,
            'total_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:' . implode(',', ProformaInvoice::STATUS_OPTIONS),
            'invoice_data' => 'sometimes|array',
        ]);

        $data = $request->only(['user_id', 'session_id', 'invoice_number', 'total_amount', 'status', 'invoice_data']);
        
        // If invoice_data is being updated, preserve variation attributes from original items
        if (isset($data['invoice_data']) && isset($data['invoice_data']['cart_items'])) {
            $originalData = $invoice->invoice_data;
            if (is_string($originalData)) {
                $originalData = json_decode($originalData, true);
            }
            
            if (isset($originalData['cart_items']) && is_array($originalData['cart_items'])) {
                $updatedItems = [];
                
                foreach ($data['invoice_data']['cart_items'] as $index => $item) {
                    // Find corresponding original item by index or product_id
                    $originalItem = $originalData['cart_items'][$index] ?? null;
                    
                    // If no match by index, try to find by product_id
                    if (!$originalItem && isset($item['product_id'])) {
                        foreach ($originalData['cart_items'] as $origItem) {
                            if (isset($origItem['product_id']) && $origItem['product_id'] == $item['product_id']) {
                                $originalItem = $origItem;
                                break;
                            }
                        }
                    }
                    
                    // Build updated item preserving variation data from original
                    $updatedItem = $item;
                    
                    if ($originalItem) {
                        // Preserve variation data if it exists in original item
                        if (isset($originalItem['product_variation_id']) && !isset($updatedItem['product_variation_id'])) {
                            $updatedItem['product_variation_id'] = $originalItem['product_variation_id'];
                        }
                        if (isset($originalItem['variation_display_name']) && !isset($updatedItem['variation_display_name'])) {
                            $updatedItem['variation_display_name'] = $originalItem['variation_display_name'];
                        }
                        if (isset($originalItem['variation_attributes']) && !isset($updatedItem['variation_attributes'])) {
                            $updatedItem['variation_attributes'] = $originalItem['variation_attributes'];
                        }
                        if (isset($originalItem['variation_sku']) && !isset($updatedItem['variation_sku'])) {
                            $updatedItem['variation_sku'] = $originalItem['variation_sku'];
                        }
                    }
                    
                    $updatedItems[] = $updatedItem;
                }
                
                $data['invoice_data']['cart_items'] = $updatedItems;
            }
        }
        
        $invoice->update(array_filter($data, fn($value) => !is_null($value)));

        return $this->sendResponse($invoice->load('user'), 'Proforma invoice updated successfully.');
    }

    /**
     * Update the status of a proforma invoice.
     *
     * @OA\Patch(
     *      path="/api/v1/proforma-invoices/{id}/status",
     *      operationId="updateProformaInvoiceStatus",
     *      tags={"Proforma Invoices"},
     *      summary="Update proforma invoice status",
     *      description="Updates only the status of a proforma invoice",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Proforma invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"status"},
     *              @OA\Property(property="status", type="string", example="Approved", enum={"Draft", "Approved", "Dispatch", "Out for Delivery", "Delivered", "Return"}),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $invoice = ProformaInvoice::find($id);

        if (is_null($invoice)) {
            return $this->sendError('Proforma invoice not found.');
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', ProformaInvoice::STATUS_OPTIONS),
        ]);

        $invoice->update(['status' => $request->status]);

        return $this->sendResponse($invoice->load('user'), 'Proforma invoice status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/proforma-invoices/{id}",
     *      operationId="deleteProformaInvoice",
     *      tags={"Proforma Invoices"},
     *      summary="Delete proforma invoice",
     *      description="Deletes a proforma invoice",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Proforma invoice id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function destroy($id)
    {
        $invoice = ProformaInvoice::find($id);

        if (is_null($invoice)) {
            return $this->sendError('Proforma invoice not found.');
        }

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

        return $this->sendResponse(null, 'Proforma invoice deleted and stock restored successfully.');
    }

    /**
     * Remove a specific item from a proforma invoice.
     * If the invoice becomes empty after removal, it will be automatically deleted.
     *
     * @OA\Delete(
     *      path="/api/v1/proforma-invoices/{id}/items/{productId}",
     *      operationId="removeProformaInvoiceItem",
     *      tags={"Proforma Invoices"},
     *      summary="Remove item from proforma invoice",
     *      description="Removes a specific item from a proforma invoice and restores stock. If the invoice becomes empty, it will be automatically deleted.",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Proforma invoice id",
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
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function removeItem($id, $productId)
    {
        $invoice = ProformaInvoice::find($id);

        if (is_null($invoice)) {
            return $this->sendError('Proforma invoice not found.');
        }

        // Decode invoice data
        $invoiceData = $invoice->invoice_data;
        if (is_string($invoiceData)) {
            $invoiceData = json_decode($invoiceData, true);
            if (is_string($invoiceData)) {
                $invoiceData = json_decode($invoiceData, true);
            }
        }

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
            'invoice' => $invoice->fresh()->load('user'),
            'removed_item' => $removedItem,
            'invoice_deleted' => false,
        ], 'Item removed from invoice and stock restored successfully.');
    }
}