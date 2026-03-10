<?php

namespace App\Http\Controllers\API;

use App\Models\WithoutGstInvoice;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * @OA\Tag(
 *     name="My Without GST Invoices",
 *     description="API Endpoints for User's Without GST Invoices"
 * )
 */
class MyWithoutGstInvoiceController extends ApiController
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
     * @param WithoutGstInvoice $invoice
     * @return array|null
     */
    private function getStoreDetails($invoice)
    {
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
                    'vendor_id' => $vendor->id,
                ];
            }
        }
        
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
     * Get authenticated user's without GST invoices
     * 
     * @OA\Get(
     *      path="/api/v1/my-without-gst-invoices",
     *      operationId="getMyWithoutGstInvoices",
     *      tags={"My Without GST Invoices"},
     *      summary="Get user's without GST invoices",
     *      description="Returns the authenticated user's without GST invoices",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
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
        $perPage = $request->input('per_page', 15);
        
        $invoices = WithoutGstInvoice::where('user_id', $user->id)
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Transform the invoices
        $transformedInvoices = $invoices->getCollection()->map(function ($invoice) {
            $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
            $itemCount = isset($invoiceData['cart_items']) ? count($invoiceData['cart_items']) : 0;
            
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => number_format($invoice->total_amount, 2, '.', ''),
                'paid_amount' => number_format($invoice->paid_amount ?? 0, 2, '.', ''),
                'pending_amount' => number_format($invoice->pending_amount ?? $invoice->total_amount, 2, '.', ''),
                'payment_status' => $invoice->payment_status ?? 'unpaid',
                'status' => $invoice->status,
                'item_count' => $itemCount,
                'vendor' => $invoice->vendor ? [
                    'id' => $invoice->vendor->id,
                    'store_name' => $invoice->vendor->store_name ?? $invoice->vendor->business_name,
                ] : null,
                'created_at' => $invoice->created_at->toIso8601String(),
                'updated_at' => $invoice->updated_at->toIso8601String(),
            ];
        });
        
        return $this->sendResponse([
            'invoices' => $transformedInvoices,
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ], 'Without GST invoices retrieved successfully.');
    }

    /**
     * Get details of a specific without GST invoice
     * 
     * @OA\Get(
     *      path="/api/v1/my-without-gst-invoices/{id}",
     *      operationId="getMyWithoutGstInvoiceDetails",
     *      tags={"My Without GST Invoices"},
     *      summary="Get without GST invoice details",
     *      description="Returns the details of a specific without GST invoice",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Invoice ID",
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
     *          description="Invoice not found"
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
        
        $invoice = WithoutGstInvoice::where('id', $id)
            ->where('user_id', $user->id)
            ->with('vendor')
            ->first();
        
        if (!$invoice) {
            return $this->sendError('Without GST invoice not found.', [], 404);
        }
        
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        $storeDetails = $this->getStoreDetails($invoice);
        
        return $this->sendResponse([
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => number_format($invoice->total_amount, 2, '.', ''),
                'paid_amount' => number_format($invoice->paid_amount ?? 0, 2, '.', ''),
                'pending_amount' => number_format($invoice->pending_amount ?? $invoice->total_amount, 2, '.', ''),
                'payment_status' => $invoice->payment_status ?? 'unpaid',
                'status' => $invoice->status,
                'invoice_data' => $invoiceData,
                'store' => $storeDetails,
                'vendor' => $invoice->vendor ? [
                    'id' => $invoice->vendor->id,
                    'store_name' => $invoice->vendor->store_name ?? $invoice->vendor->business_name,
                    'business_email' => $invoice->vendor->business_email,
                    'business_phone' => $invoice->vendor->business_phone,
                ] : null,
                'created_at' => $invoice->created_at->toIso8601String(),
                'updated_at' => $invoice->updated_at->toIso8601String(),
            ],
        ], 'Without GST invoice details retrieved successfully.');
    }

    /**
     * Download without GST invoice as PDF
     * 
     * @OA\Get(
     *      path="/api/v1/my-without-gst-invoices/{id}/download-pdf",
     *      operationId="downloadMyWithoutGstInvoicePdf",
     *      tags={"My Without GST Invoices"},
     *      summary="Download without GST invoice PDF",
     *      description="Download the without GST invoice as a PDF file",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Invoice ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="PDF file download",
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
     *          description="Invoice not found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function downloadPdf(Request $request, $id)
    {
        $user = $request->user();
        
        $invoice = WithoutGstInvoice::where('id', $id)
            ->where('user_id', $user->id)
            ->with('vendor')
            ->first();
        
        if (!$invoice) {
            return $this->sendError('Without GST invoice not found.', [], 404);
        }
        
        $invoiceData = $this->decodeInvoiceData($invoice->invoice_data);
        
        // Generate PDF using the frontend template
        $pdf = Pdf::loadView('frontend.without-gst-invoice-pdf', [
            'invoice' => $invoice,
            'invoiceData' => $invoiceData,
        ]);
        
        $filename = 'without-gst-invoice-' . $invoice->invoice_number . '.pdf';
        
        return $pdf->download($filename);
    }
}
