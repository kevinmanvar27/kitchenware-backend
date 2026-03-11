<?php

namespace App\Http\Controllers\Api\VendorCustomer;

use App\Http\Controllers\Controller;
use App\Models\ProductReturn;
use App\Models\ProformaInvoice;
use App\Models\VendorCustomer;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReturnController extends Controller
{
    /**
     * Get eligible invoices for return
     */
    public function eligibleInvoices(Request $request)
    {
        $vendorCustomer = auth()->user();
        
        if (!$vendorCustomer instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        // Get return window from vendor settings (default 7 days)
        $returnWindowDays = $vendorCustomer->vendor->return_window_days ?? 7;
        $returnWindowDate = Carbon::now()->subDays($returnWindowDays);
        
        $eligibleInvoices = ProformaInvoice::where('vendor_customer_id', $vendorCustomer->id)
            ->where('status', ProformaInvoice::STATUS_DELIVERED)
            ->where('created_at', '>=', $returnWindowDate)
            ->whereDoesntHave('returns', function($q) {
                $q->whereIn('status', ['pending', 'under_review', 'approved', 'pickup_scheduled', 'picked_up', 'received', 'inspected', 'refund_processing']);
            })
            ->with(['vendor'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'invoices' => $eligibleInvoices,
                'return_window_days' => $returnWindowDays,
                'message' => "You can return products within {$returnWindowDays} days of delivery"
            ]
        ]);
    }
    
    /**
     * Get return reasons
     */
    public function returnReasons()
    {
        $reasons = [
            ['id' => 'damaged', 'label' => 'Product Damaged', 'requires_image' => true],
            ['id' => 'wrong_item', 'label' => 'Wrong Item Delivered', 'requires_image' => true],
            ['id' => 'quality_issue', 'label' => 'Quality Issue', 'requires_image' => true],
            ['id' => 'expired', 'label' => 'Expired Product', 'requires_image' => true],
            ['id' => 'not_as_described', 'label' => 'Not As Described', 'requires_image' => false],
            ['id' => 'defective', 'label' => 'Defective Product', 'requires_image' => true],
            ['id' => 'changed_mind', 'label' => 'Changed My Mind', 'requires_image' => false],
            ['id' => 'other', 'label' => 'Other Reason', 'requires_image' => false],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $reasons
        ]);
    }
    
    /**
     * Create return request
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:proforma_invoices,id',
            'return_items' => 'required|array|min:1',
            'return_items.*.product_id' => 'required|exists:products,id',
            'return_items.*.quantity_returned' => 'required|integer|min:1',
            'return_items.*.variation_id' => 'nullable|exists:product_variations,id',
            'reason_category' => 'required|string',
            'reason_description' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'pickup_address' => 'nullable|string',
            'pickup_contact' => 'nullable|string',
        ]);
        
        $vendorCustomer = auth()->user();
        
        if (!$vendorCustomer instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        // Verify invoice belongs to customer and is eligible
        $invoice = ProformaInvoice::where('id', $request->invoice_id)
            ->where('vendor_customer_id', $vendorCustomer->id)
            ->where('status', ProformaInvoice::STATUS_DELIVERED)
            ->first();
        
        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found or not eligible for return'
            ], 404);
        }
        
        // Check return window
        $returnWindowDays = $invoice->vendor->return_window_days ?? 7;
        $returnWindowDate = Carbon::now()->subDays($returnWindowDays);
        
        if ($invoice->created_at < $returnWindowDate) {
            return response()->json([
                'success' => false,
                'message' => "Return window of {$returnWindowDays} days has expired"
            ], 422);
        }
        
        // Check if return already exists
        $existingReturn = ProductReturn::where('proforma_invoice_id', $invoice->id)
            ->whereIn('status', ['pending', 'under_review', 'approved', 'pickup_scheduled', 'picked_up', 'received', 'inspected', 'refund_processing'])
            ->first();
        
        if ($existingReturn) {
            return response()->json([
                'success' => false,
                'message' => 'A return request already exists for this invoice'
            ], 422);
        }
        
        DB::beginTransaction();
        try {
            // Process return items and calculate return amount
            $returnItems = [];
            $returnAmount = 0;
            $invoiceData = $invoice->invoice_data;
            
            foreach ($request->return_items as $item) {
                // Find item in invoice
                $invoiceItem = collect($invoiceData['cart_items'])->first(function($cartItem) use ($item) {
                    $productMatch = $cartItem['product_id'] == $item['product_id'];
                    $variationMatch = isset($item['variation_id']) 
                        ? (isset($cartItem['variation_id']) && $cartItem['variation_id'] == $item['variation_id'])
                        : true;
                    return $productMatch && $variationMatch;
                });
                
                if (!$invoiceItem) {
                    throw new \Exception('Invalid product in return request');
                }
                
                if ($item['quantity_returned'] > $invoiceItem['quantity']) {
                    throw new \Exception('Return quantity exceeds ordered quantity');
                }
                
                $itemReturnAmount = $invoiceItem['price'] * $item['quantity_returned'];
                $returnAmount += $itemReturnAmount;
                
                $returnItems[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $invoiceItem['product_name'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'variation_name' => $invoiceItem['variation_name'] ?? null,
                    'quantity_ordered' => $invoiceItem['quantity'],
                    'quantity_returned' => $item['quantity_returned'],
                    'price' => $invoiceItem['price'],
                    'return_amount' => $itemReturnAmount,
                ];
            }
            
            // Upload images if provided
            $imageUrls = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('returns/' . $invoice->vendor_id, 'public');
                    $imageUrls[] = Storage::url($path);
                }
            }
            
            // Generate return number
            $returnNumber = 'RET-' . $invoice->vendor_id . '-' . str_pad(
                ProductReturn::where('vendor_id', $invoice->vendor_id)->count() + 1,
                5, '0', STR_PAD_LEFT
            );
            
            // Create return request
            $return = ProductReturn::create([
                'vendor_id' => $invoice->vendor_id,
                'vendor_customer_id' => $vendorCustomer->id,
                'user_id' => null,
                'proforma_invoice_id' => $invoice->id,
                'return_number' => $returnNumber,
                'return_items' => $returnItems,
                'return_amount' => $returnAmount,
                'return_type' => count($returnItems) == count($invoiceData['cart_items']) ? 'full' : 'partial',
                'reason_category' => $request->reason_category,
                'reason_description' => $request->reason_description,
                'images' => $imageUrls,
                'pickup_address' => $request->pickup_address ?? $vendorCustomer->address,
                'pickup_contact' => $request->pickup_contact ?? $vendorCustomer->mobile_number,
                'device_type' => $request->header('X-Device-Type', 'mobile'),
                'app_version' => $request->header('X-App-Version'),
                'ip_address' => $request->ip(),
                'status' => 'pending',
            ]);
            
            // Send notification to vendor
            Notification::create([
                'user_id' => $invoice->vendor->user_id, // Vendor owner
                'title' => 'New Return Request',
                'message' => "Return request {$returnNumber} received from {$vendorCustomer->name}",
                'type' => 'return_request',
                'data' => json_encode([
                    'return_id' => $return->id,
                    'return_number' => $returnNumber,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $vendorCustomer->name,
                    'return_amount' => $returnAmount,
                ]),
            ]);
            
            // TODO: Send push notification to vendor if device_token exists
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Return request submitted successfully',
                'data' => [
                    'return' => $return->load(['invoice', 'vendor']),
                    'return_number' => $returnNumber,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get customer's return requests
     */
    public function index(Request $request)
    {
        $vendorCustomer = auth()->user();
        
        if (!$vendorCustomer instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $returns = ProductReturn::where('vendor_customer_id', $vendorCustomer->id)
            ->with(['invoice', 'vendor'])
            ->when($request->status, function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $returns
        ]);
    }
    
    /**
     * Get specific return details
     */
    public function show($id)
    {
        $vendorCustomer = auth()->user();
        
        if (!$vendorCustomer instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $return = ProductReturn::where('id', $id)
            ->where('vendor_customer_id', $vendorCustomer->id)
            ->with(['invoice', 'vendor', 'reviewedBy'])
            ->first();
        
        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $return
        ]);
    }
    
    /**
     * Cancel return request
     */
    public function cancel($id)
    {
        $vendorCustomer = auth()->user();
        
        if (!$vendorCustomer instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $return = ProductReturn::where('id', $id)
            ->where('vendor_customer_id', $vendorCustomer->id)
            ->where('status', 'pending')
            ->first();
        
        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found or cannot be cancelled'
            ], 404);
        }
        
        $return->update(['status' => 'cancelled']);
        
        return response()->json([
            'success' => true,
            'message' => 'Return request cancelled successfully'
        ]);
    }
    
    /**
     * Upload additional images for return request
     */
    public function uploadImages(Request $request, $id)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);
        
        $vendorCustomer = auth()->user();
        
        if (!$vendorCustomer instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $return = ProductReturn::where('id', $id)
            ->where('vendor_customer_id', $vendorCustomer->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->first();
        
        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found or cannot upload images'
            ], 404);
        }
        
        // Check total image count
        $existingImages = $return->images ?? [];
        if (count($existingImages) + count($request->file('images')) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum 5 images allowed per return request'
            ], 422);
        }
        
        $imageUrls = $existingImages;
        foreach ($request->file('images') as $image) {
            $path = $image->store('returns/' . $return->vendor_id, 'public');
            $imageUrls[] = Storage::url($path);
        }
        
        $return->update(['images' => $imageUrls]);
        
        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'data' => [
                'images' => $imageUrls
            ]
        ]);
    }
    
    /**
     * Get status history for return request
     */
    public function statusHistory($id)
    {
        $vendorCustomer = auth()->user();
        
        if (!$vendorCustomer instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $return = ProductReturn::where('id', $id)
            ->where('vendor_customer_id', $vendorCustomer->id)
            ->first();
        
        if (!$return) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found'
            ], 404);
        }
        
        // Build status history from return data
        $history = [];
        
        // Status timeline based on current status
        $statusFlow = [
            'pending' => ['label' => 'Request Submitted', 'date' => $return->created_at],
            'under_review' => ['label' => 'Under Review', 'date' => $return->reviewed_at],
            'approved' => ['label' => 'Approved', 'date' => $return->approved_at],
            'rejected' => ['label' => 'Rejected', 'date' => $return->rejected_at],
            'pickup_scheduled' => ['label' => 'Pickup Scheduled', 'date' => $return->pickup_scheduled_at],
            'picked_up' => ['label' => 'Picked Up', 'date' => $return->picked_up_at],
            'received' => ['label' => 'Received at Warehouse', 'date' => $return->received_at],
            'inspected' => ['label' => 'Quality Inspected', 'date' => $return->inspected_at],
            'refund_processing' => ['label' => 'Refund Processing', 'date' => $return->refund_initiated_at],
            'completed' => ['label' => 'Completed', 'date' => $return->refund_completed_at],
            'cancelled' => ['label' => 'Cancelled', 'date' => $return->updated_at],
        ];
        
        foreach ($statusFlow as $status => $info) {
            if ($info['date']) {
                $history[] = [
                    'status' => $status,
                    'label' => $info['label'],
                    'date' => $info['date'],
                    'is_current' => $return->status === $status,
                ];
            }
            
            // Stop at current status (unless rejected or cancelled)
            if ($return->status === $status && !in_array($status, ['rejected', 'cancelled'])) {
                break;
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'current_status' => $return->status,
                'history' => $history,
                'rejection_reason' => $return->rejection_reason,
                'admin_notes' => $return->admin_notes,
            ]
        ]);
    }
}
