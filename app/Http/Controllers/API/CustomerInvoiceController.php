<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProformaInvoice;
use App\Models\ShoppingCartItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\VendorCustomer;
use App\Services\InvoicePaymentService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * @OA\Tag(
 *     name="Customer Invoices",
 *     description="API Endpoints for Vendor Customer Invoices"
 * )
 */
class CustomerInvoiceController extends Controller
{
    protected $invoicePaymentService;
    protected $razorpayService;

    /**
     * Constructor
     */
    public function __construct(
        InvoicePaymentService $invoicePaymentService,
        RazorpayService $razorpayService
    ) {
        $this->invoicePaymentService = $invoicePaymentService;
        $this->razorpayService = $razorpayService;
    }

    /**
     * Get the authenticated customer
     */
    private function getCustomer(Request $request): VendorCustomer
    {
        return $request->user();
    }

    /**
     * Decode invoice data
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
     * Get customer's invoices
     */
    public function index(Request $request)
    {
        $customer = $this->getCustomer($request);
        
        $query = ProformaInvoice::where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->orderBy('created_at', 'desc');
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment_status if provided
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        $perPage = min($request->get('per_page', 15), 50);
        $invoices = $query->paginate($perPage);
        
        // Transform invoices
        $invoices->getCollection()->transform(function ($invoice) {
            $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount,
                'pending_amount' => $invoice->pending_amount,
                'payment_status' => $invoice->payment_status,
                'status' => $invoice->status,
                'items_count' => isset($invoiceData['cart_items']) ? count($invoiceData['cart_items']) : 0,
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Invoices retrieved successfully',
            'data' => $invoices
        ]);
    }

    /**
     * Get invoice details
     */
    public function show(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $vendor = $customer->vendor;

        // Ensure store details are in invoice data
        if (!isset($invoiceData['store']) && $vendor) {
            $invoiceData['store'] = [
                'id' => $vendor->id,
                'store_name' => $vendor->store_name,
                'store_slug' => $vendor->store_slug,
                'store_logo_url' => $vendor->store_logo_url,
                'store_banner_url' => $vendor->store_banner_url,
                'banner_redirect_url' => $vendor->banner_redirect_url,
                'business_name' => $vendor->business_name,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice retrieved successfully',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'pending_amount' => $invoice->pending_amount,
                    'payment_status' => $invoice->payment_status,
                    'status' => $invoice->status,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ],
                'invoice_data' => $invoiceData,
            ]
        ]);
    }

    /**
     * Record a payment for an invoice
     * 
     * @OA\Post(
     *     path="/api/v1/customer/invoices/{id}/pay",
     *     summary="Record a payment for an invoice",
     *     tags={"Customer Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", description="Payment amount"),
     *             @OA\Property(property="payment_method", type="string", description="Payment method (cash, upi, card, bank_transfer, etc.)"),
     *             @OA\Property(property="transaction_id", type="string", description="Transaction reference ID"),
     *             @OA\Property(property="notes", type="string", description="Payment notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment recorded successfully"
     *     )
     * )
     */
    public function recordPayment(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Check if invoice is already fully paid
        if ($invoice->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already fully paid',
                'data' => null
            ], 400);
        }

        // Calculate pending amount
        $pendingAmount = $invoice->total_amount - $invoice->paid_amount;

        // Validate request
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $pendingAmount,
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $paymentAmount = (float) $request->amount;
        $newPaidAmount = $invoice->paid_amount + $paymentAmount;

        // Determine new payment status
        if ($newPaidAmount >= $invoice->total_amount) {
            $paymentStatus = 'paid';
            $newPaidAmount = $invoice->total_amount; // Ensure we don't overpay
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        // Ensure vendor_id is set
        if (!$invoice->vendor_id && $customer->vendor_id) {
            $invoice->vendor_id = $customer->vendor_id;
            $invoice->save();
            
            \Illuminate\Support\Facades\Log::info('Updated missing vendor_id on invoice', [
                'invoice_id' => $invoice->id,
                'vendor_id' => $customer->vendor_id
            ]);
        }

        // Update invoice
        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
        ]);
        
        // Process vendor earnings if payment is completed
        if ($paymentStatus === 'paid') {
            $this->invoicePaymentService->processInvoicePayment($invoice);
        }

        // Optionally store payment history in invoice_data
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data) ?? [];
        if (!isset($invoiceData['payment_history'])) {
            $invoiceData['payment_history'] = [];
        }
        
        $invoiceData['payment_history'][] = [
            'amount' => $paymentAmount,
            'payment_method' => $request->payment_method ?? 'unknown',
            'transaction_id' => $request->transaction_id ?? null,
            'notes' => $request->notes ?? null,
            'paid_by' => 'customer',
            'paid_at' => now()->toISOString(),
        ];
        
        $invoice->update(['invoice_data' => $invoiceData]);

        return response()->json([
            'success' => true,
            'message' => 'Payment of ₹' . number_format($paymentAmount, 2) . ' recorded successfully',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $newPaidAmount,
                    'pending_amount' => $invoice->total_amount - $newPaidAmount,
                    'payment_status' => $paymentStatus,
                    'status' => $invoice->status,
                ],
                'payment' => [
                    'amount' => $paymentAmount,
                    'payment_method' => $request->payment_method ?? 'unknown',
                    'transaction_id' => $request->transaction_id ?? null,
                ]
            ]
        ]);
    }

    /**
     * Pay full pending amount for an invoice
     * 
     * @OA\Post(
     *     path="/api/v1/customer/invoices/{id}/pay-full",
     *     summary="Pay full pending amount for an invoice",
     *     tags={"Customer Invoices"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_method", type="string", description="Payment method"),
     *             @OA\Property(property="transaction_id", type="string", description="Transaction reference ID"),
     *             @OA\Property(property="notes", type="string", description="Payment notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Full payment recorded successfully"
     *     )
     * )
     */
    public function payFull(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Check if invoice is already fully paid
        if ($invoice->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already fully paid',
                'data' => null
            ], 400);
        }

        // Validate request
        $request->validate([
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // Ensure vendor_id is set
        if (!$invoice->vendor_id && $customer->vendor_id) {
            $invoice->vendor_id = $customer->vendor_id;
            $invoice->save();
            
            \Illuminate\Support\Facades\Log::info('Updated missing vendor_id on invoice', [
                'invoice_id' => $invoice->id,
                'vendor_id' => $customer->vendor_id
            ]);
        }

        $pendingAmount = $invoice->total_amount - $invoice->paid_amount;

        // Update invoice to fully paid
        $invoice->update([
            'paid_amount' => $invoice->total_amount,
            'payment_status' => 'paid',
        ]);
        
        // Process vendor earnings for the completed payment
        $this->invoicePaymentService->processInvoicePayment($invoice);

        // Store payment history in invoice_data
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data) ?? [];
        if (!isset($invoiceData['payment_history'])) {
            $invoiceData['payment_history'] = [];
        }
        
        $invoiceData['payment_history'][] = [
            'amount' => $pendingAmount,
            'payment_method' => $request->payment_method ?? 'unknown',
            'transaction_id' => $request->transaction_id ?? null,
            'notes' => $request->notes ?? null,
            'paid_by' => 'customer',
            'paid_at' => now()->toISOString(),
            'is_full_payment' => true,
        ];
        
        $invoice->update(['invoice_data' => $invoiceData]);

        return response()->json([
            'success' => true,
            'message' => 'Full payment of ₹' . number_format($pendingAmount, 2) . ' recorded successfully',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->total_amount,
                    'pending_amount' => 0,
                    'payment_status' => 'paid',
                    'status' => $invoice->status,
                ],
                'payment' => [
                    'amount' => $pendingAmount,
                    'payment_method' => $request->payment_method ?? 'unknown',
                    'transaction_id' => $request->transaction_id ?? null,
                ]
            ]
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function downloadPdf(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $vendor = $customer->vendor;

        // Build store details for vendor
        $store = null;
        if ($vendor) {
            $store = [
                'store_name' => $vendor->store_name ?? $vendor->business_name ?? (function_exists('setting') ? setting('site_title', 'Store') : 'Store'),
                'store_logo' => $vendor->store_logo,
                'business_email' => $vendor->business_email,
                'business_phone' => $vendor->business_phone,
                'business_address' => $vendor->business_address,
                'full_address' => implode(', ', array_filter([
                    $vendor->business_address,
                    $vendor->city,
                    $vendor->state,
                    $vendor->postal_code
                ])),
                'city' => $vendor->city,
                'state' => $vendor->state,
                'postal_code' => $vendor->postal_code,
                'gst_number' => $vendor->gst_number,
                'vendor_id' => $vendor->id,
            ];
        }

        // Prepare data for PDF
        $data = [
            'invoice' => $invoice,
            'invoiceData' => $invoiceData,
            'siteTitle' => $vendor->store_name ?? (function_exists('setting') ? setting('site_title', 'Store') : 'Store'),
            'companyAddress' => $vendor->business_address ?? (function_exists('setting') ? setting('address', '') : ''),
            'companyEmail' => $vendor->business_email ?? (function_exists('setting') ? setting('email', '') : ''),
            'companyPhone' => $vendor->business_phone ?? (function_exists('setting') ? setting('phone', '') : ''),
            'companyLogo' => $vendor->store_logo_url ?? null,
            'store' => $store,
        ];

        // Load PDF view
        $pdf = Pdf::loadView('frontend.proforma-invoice-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        // If invoice is approved, name it as 'invoice' instead of 'proforma-invoice'
        $filePrefix = ($invoice->status === \App\Models\ProformaInvoice::STATUS_APPROVED) 
            ? 'invoice' 
            : 'proforma-invoice';
        return $pdf->download($filePrefix . '-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Add invoice items back to cart
     */
    public function addToCart(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Check if invoice is in draft status
        if ($invoice->status !== ProformaInvoice::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be added to cart',
                'data' => null
            ], 400);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $addedItems = [];
        $skippedItems = [];

        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product || $product->vendor_id != $customer->vendor_id) {
                    $skippedItems[] = $item['product_name'] ?? 'Unknown Product';
                    continue;
                }

                // Check if item already exists in cart
                $existingCartItem = ShoppingCartItem::where('vendor_customer_id', $customer->id)
                    ->where('product_id', $item['product_id'])
                    ->where('product_variation_id', $item['product_variation_id'] ?? null)
                    ->first();

                if ($existingCartItem) {
                    $existingCartItem->update([
                        'quantity' => $existingCartItem->quantity + $item['quantity'],
                        'price' => $item['price']
                    ]);
                } else {
                    ShoppingCartItem::create([
                        'vendor_customer_id' => $customer->id,
                        'product_id' => $item['product_id'],
                        'product_variation_id' => $item['product_variation_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
                
                $addedItems[] = $item['product_name'];
            }
        }

        // Delete the invoice
        $invoice->delete();

        $message = 'Products from invoice added to cart successfully.';
        if (!empty($skippedItems)) {
            $message .= ' Some items were skipped: ' . implode(', ', $skippedItems);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'added_items' => $addedItems,
                'skipped_items' => $skippedItems,
                'cart_count' => ShoppingCartItem::where('vendor_customer_id', $customer->id)->count(),
            ]
        ]);
    }

    /**
     * Delete an invoice
     */
    public function destroy(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Restore stock for all items
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        
        if (isset($invoiceData['cart_items']) && is_array($invoiceData['cart_items'])) {
            foreach ($invoiceData['cart_items'] as $item) {
                if (!empty($item['product_variation_id'])) {
                    $variation = ProductVariation::find($item['product_variation_id']);
                    if ($variation) {
                        $variation->increment('stock_quantity', $item['quantity']);
                        if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                            $variation->update(['in_stock' => true]);
                        }
                    }
                } else {
                    $product = Product::find($item['product_id'] ?? null);
                    if ($product) {
                        $product->increment('stock_quantity', $item['quantity']);
                        if ($product->fresh()->stock_quantity > 0 && !$product->in_stock) {
                            $product->update(['in_stock' => true]);
                        }
                    }
                }
            }
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted and stock restored successfully',
            'data' => null
        ]);
    }

    /**
     * Remove a specific item from invoice
     */
    public function removeItem(Request $request, $id, $productId)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);

        if (!isset($invoiceData['cart_items']) || !is_array($invoiceData['cart_items'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice has no items',
                'data' => null
            ], 400);
        }

        $itemFound = false;
        $removedItem = null;
        $newCartItems = [];

        foreach ($invoiceData['cart_items'] as $item) {
            if (($item['product_id'] ?? null) == $productId) {
                $itemFound = true;
                $removedItem = $item;
                
                // Restore stock
                if (!empty($item['product_variation_id'])) {
                    $variation = ProductVariation::find($item['product_variation_id']);
                    if ($variation) {
                        $variation->increment('stock_quantity', $item['quantity']);
                        if ($variation->fresh()->stock_quantity > 0 && !$variation->in_stock) {
                            $variation->update(['in_stock' => true]);
                        }
                    }
                } else {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->increment('stock_quantity', $item['quantity']);
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
            return response()->json([
                'success' => false,
                'message' => 'Item not found in invoice',
                'data' => null
            ], 404);
        }

        // If invoice is empty, delete it
        if (empty($newCartItems)) {
            $invoice->delete();
            return response()->json([
                'success' => true,
                'message' => 'Last item removed. Invoice has been deleted.',
                'data' => [
                    'invoice_deleted' => true,
                    'removed_item' => $removedItem,
                ]
            ]);
        }

        // Update invoice
        $invoiceData['cart_items'] = $newCartItems;
        $newTotal = array_reduce($newCartItems, function ($carry, $item) {
            return $carry + (($item['price'] ?? 0) * ($item['quantity'] ?? 0));
        }, 0);
        
        $invoiceData['total'] = $newTotal;

        $invoice->update([
            'invoice_data' => $invoiceData,
            'total_amount' => $newTotal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from invoice successfully',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $newTotal,
                ],
                'removed_item' => $removedItem,
                'invoice_deleted' => false,
            ]
        ]);
    }

    /**
     * Create Razorpay order for invoice payment
     * 
     * @OA\Post(
     *     path="/api/vendor-customer/invoices/{id}/create-payment-order",
     *     tags={"Customer Invoices"},
     *     summary="Create Razorpay order for invoice payment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order created successfully"),
     *     @OA\Response(response=404, description="Invoice not found"),
     *     @OA\Response(response=400, description="Invoice already paid")
     * )
     */
    public function createPaymentOrder(Request $request, $id)
    {
        $customer = $this->getCustomer($request);
        
        $invoice = ProformaInvoice::where('id', $id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Check if invoice is already paid
        if ($invoice->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already fully paid',
                'data' => null
            ], 400);
        }

        // Calculate pending amount
        $pendingAmount = $invoice->total_amount - $invoice->paid_amount;

        // Check if Razorpay is configured
        if (!$this->razorpayService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is not configured. Please contact support.',
                'data' => null
            ], 500);
        }

        // Create Razorpay order with Route (automatic split)
        $result = $this->razorpayService->createOrderWithRoute($invoice, $pendingAmount);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: ' . ($result['error'] ?? 'Unknown error'),
                'data' => null
            ], 500);
        }

        // Store order ID in invoice
        $invoice->update([
            'razorpay_order_id' => $result['order_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment order created successfully',
            'data' => [
                'order_id' => $result['order_id'],
                'amount' => $result['amount'], // Amount in paise
                'currency' => $result['currency'],
                'key_id' => $result['key_id'],
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'pending_amount' => $pendingAmount,
                ],
            ]
        ]);
    }

    /**
     * Verify payment and update invoice
     * 
     * @OA\Post(
     *     path="/api/vendor-customer/invoices/verify-payment",
     *     tags={"Customer Invoices"},
     *     summary="Verify Razorpay payment",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"razorpay_order_id","razorpay_payment_id","razorpay_signature"},
     *             @OA\Property(property="razorpay_order_id", type="string"),
     *             @OA\Property(property="razorpay_payment_id", type="string"),
     *             @OA\Property(property="razorpay_signature", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment verified successfully"),
     *     @OA\Response(response=404, description="Invoice not found"),
     *     @OA\Response(response=400, description="Payment verification failed")
     * )
     */
    public function verifyPayment(Request $request)
    {
        $customer = $this->getCustomer($request);

        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        // Find invoice by order ID
        $invoice = ProformaInvoice::where('razorpay_order_id', $request->razorpay_order_id)
            ->where('vendor_customer_id', $customer->id)
            ->where('vendor_id', $customer->vendor_id)
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'data' => null
            ], 404);
        }

        // Verify payment signature
        $isValid = $this->razorpayService->verifyPaymentSignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if (!$isValid) {
            \Illuminate\Support\Facades\Log::warning('Payment verification failed', [
                'invoice_id' => $invoice->id,
                'order_id' => $request->razorpay_order_id,
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Invalid signature.',
                'data' => null
            ], 400);
        }

        // Get payment details
        $paymentDetails = $this->razorpayService->getPaymentDetails($request->razorpay_payment_id);

        // Calculate pending amount
        $pendingAmount = $invoice->total_amount - $invoice->paid_amount;

        // Update invoice
        $invoice->update([
            'paid_amount' => $invoice->paid_amount + $pendingAmount,
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);

        // Process vendor earnings (this will update vendor's pending balance)
        $this->invoicePaymentService->processInvoicePayment($invoice);

        // Add payment to history
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data) ?? [];
        if (!isset($invoiceData['payment_history'])) {
            $invoiceData['payment_history'] = [];
        }

        $invoiceData['payment_history'][] = [
            'amount' => $pendingAmount,
            'payment_method' => 'razorpay',
            'transaction_id' => $request->razorpay_payment_id,
            'notes' => 'Online payment via Razorpay (Auto-routed to RazorpayX)',
            'paid_by' => 'customer',
            'paid_at' => now()->toISOString(),
            'payment_details' => $paymentDetails,
        ];

        $invoice->update(['invoice_data' => $invoiceData]);

        \Illuminate\Support\Facades\Log::info('Payment verified and invoice updated', [
            'invoice_id' => $invoice->id,
            'payment_id' => $request->razorpay_payment_id,
            'customer_id' => $customer->id,
            'amount' => $pendingAmount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'paid_amount' => $invoice->paid_amount,
                    'pending_amount' => 0,
                    'payment_status' => $invoice->payment_status,
                    'payment_method' => $invoice->payment_method,
                ],
                'payment' => [
                    'payment_id' => $request->razorpay_payment_id,
                    'amount' => $pendingAmount,
                    'status' => 'success',
                ],
            ]
        ]);
    }
}
