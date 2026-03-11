<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductReturn;
use App\Models\ProformaInvoice;
use App\Models\VendorCustomer;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Notification;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReturnController extends Controller
{
    use LogsActivity;
    
    /**
     * Display a listing of return requests
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        if (!$vendor) {
            abort(403, 'Unauthorized access');
        }
        
        $query = ProductReturn::where('vendor_id', $vendor->id)
            ->with(['user', 'vendorCustomer', 'invoice', 'reviewedBy']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by customer type
        if ($request->filled('customer_type')) {
            if ($request->customer_type === 'vendor_customer') {
                $query->whereNotNull('vendor_customer_id');
            } elseif ($request->customer_type === 'user') {
                $query->whereNotNull('user_id');
            }
        }
        
        // Search by return number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vendorCustomer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $returns = $query->paginate(20);
        
        // Get statistics
        $pendingCount = ProductReturn::where('vendor_id', $vendor->id)->where('status', 'pending')->count();
        $underReviewCount = ProductReturn::where('vendor_id', $vendor->id)->where('status', 'under_review')->count();
        $completedCount = ProductReturn::where('vendor_id', $vendor->id)->where('status', 'completed')->count();
        $rejectedCount = ProductReturn::where('vendor_id', $vendor->id)->where('status', 'rejected')->count();
        
        return view('vendor.returns.index', compact(
            'returns',
            'pendingCount',
            'underReviewCount',
            'completedCount',
            'rejectedCount'
        ));
    }
    
    /**
     * Display the specified return request
     */
    public function show($id)
    {
        $vendor = Auth::user()->vendor;
        
        if (!$vendor) {
            abort(403, 'Unauthorized access');
        }
        
        $return = ProductReturn::where('vendor_id', $vendor->id)
            ->with(['user', 'vendorCustomer', 'invoice', 'reviewedBy'])
            ->findOrFail($id);
        
        return view('vendor.returns.show', compact('return'));
    }
    
    /**
     * Update return status to under review
     */
    public function markUnderReview($id)
    {
        $vendor = Auth::user()->vendor;
        
        $return = ProductReturn::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->findOrFail($id);
        
        $return->update([
            'status' => 'under_review',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);
        
        $this->logActivity('Updated Return Status', "Marked return {$return->return_number} as under review", $return);
        
        // Send notification to customer
        $this->sendNotificationToCustomer($return, 'Return Under Review', 'Your return request is being reviewed by the vendor.');
        
        return redirect()->back()->with('success', 'Return marked as under review');
    }
    
    /**
     * Approve return request
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'pickup_scheduled_at' => 'nullable|date|after:now',
        ]);
        
        $vendor = Auth::user()->vendor;
        
        $return = ProductReturn::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->findOrFail($id);
        
        DB::beginTransaction();
        try {
            $return->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'approved_at' => now(),
                'admin_notes' => $request->admin_notes,
                'pickup_scheduled_at' => $request->pickup_scheduled_at,
            ]);
            
            $this->logActivity('Approved Return', "Approved return {$return->return_number}", $return);
            
            // Send notification to customer
            $message = "Your return request {$return->return_number} has been approved.";
            if ($request->pickup_scheduled_at) {
                $message .= " Pickup scheduled for " . Carbon::parse($request->pickup_scheduled_at)->format('M d, Y h:i A');
            }
            $this->sendNotificationToCustomer($return, 'Return Approved', $message);
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Return request approved successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve return: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject return request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);
        
        $vendor = Auth::user()->vendor;
        
        $return = ProductReturn::where('vendor_id', $vendor->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->findOrFail($id);
        
        DB::beginTransaction();
        try {
            $return->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);
            
            $this->logActivity('Rejected Return', "Rejected return {$return->return_number}", $return);
            
            // Send notification to customer
            $this->sendNotificationToCustomer(
                $return, 
                'Return Rejected', 
                "Your return request {$return->return_number} has been rejected. Reason: {$request->rejection_reason}"
            );
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Return request rejected');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject return: ' . $e->getMessage());
        }
    }
    
    /**
     * Update return status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pickup_scheduled,picked_up,received,inspected,refund_processing',
            'notes' => 'nullable|string|max:1000',
            'pickup_scheduled_at' => 'nullable|required_if:status,pickup_scheduled|date',
        ]);
        
        $vendor = Auth::user()->vendor;
        
        $return = ProductReturn::where('vendor_id', $vendor->id)->findOrFail($id);
        
        // Validate status transition
        $allowedTransitions = [
            'approved' => ['pickup_scheduled'],
            'pickup_scheduled' => ['picked_up'],
            'picked_up' => ['received'],
            'received' => ['inspected'],
            'inspected' => ['refund_processing'],
        ];
        
        if (!isset($allowedTransitions[$return->status]) || 
            !in_array($request->status, $allowedTransitions[$return->status])) {
            return redirect()->back()->with('error', 'Invalid status transition');
        }
        
        DB::beginTransaction();
        try {
            $updateData = [
                'status' => $request->status,
                'admin_notes' => $request->notes,
            ];
            
            // Set timestamp for specific statuses
            switch ($request->status) {
                case 'pickup_scheduled':
                    $updateData['pickup_scheduled_at'] = $request->pickup_scheduled_at;
                    break;
                case 'picked_up':
                    $updateData['picked_up_at'] = now();
                    break;
                case 'received':
                    $updateData['received_at'] = now();
                    break;
                case 'inspected':
                    $updateData['inspected_at'] = now();
                    $updateData['inspection_notes'] = $request->notes;
                    break;
                case 'refund_processing':
                    $updateData['refund_initiated_at'] = now();
                    break;
            }
            
            $return->update($updateData);
            
            $this->logActivity('Updated Return Status', "Updated return {$return->return_number} to {$request->status}", $return);
            
            // Send notification to customer
            $statusLabels = [
                'pickup_scheduled' => 'Pickup Scheduled',
                'picked_up' => 'Product Picked Up',
                'received' => 'Product Received',
                'inspected' => 'Quality Inspected',
                'refund_processing' => 'Refund Processing',
            ];
            
            $this->sendNotificationToCustomer(
                $return,
                $statusLabels[$request->status],
                "Your return {$return->return_number} status updated to: {$statusLabels[$request->status]}"
            );
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Return status updated successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
    
    /**
     * Process refund
     */
    public function processRefund(Request $request, $id)
    {
        $request->validate([
            'refund_method' => 'required|in:wallet,original_method,bank_transfer,cash',
            'refund_amount' => 'required|numeric|min:0',
            'refund_reference' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $vendor = Auth::user()->vendor;
        
        $return = ProductReturn::where('vendor_id', $vendor->id)
            ->where('status', 'refund_processing')
            ->findOrFail($id);
        
        if ($request->refund_amount > $return->return_amount) {
            return redirect()->back()->with('error', 'Refund amount cannot exceed return amount');
        }
        
        DB::beginTransaction();
        try {
            // Process wallet refund
            if ($request->refund_method === 'wallet') {
                if ($return->vendor_customer_id) {
                    // Refund to vendor customer wallet
                    $customer = $return->vendorCustomer;
                    $customer->increment('wallet_balance', $request->refund_amount);
                    
                    WalletTransaction::create([
                        'vendor_customer_id' => $customer->id,
                        'type' => 'credit',
                        'amount' => $request->refund_amount,
                        'description' => "Refund for return {$return->return_number}",
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                    ]);
                } elseif ($return->user_id) {
                    // Refund to user wallet
                    $user = $return->user;
                    $user->increment('wallet_balance', $request->refund_amount);
                    
                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'credit',
                        'amount' => $request->refund_amount,
                        'description' => "Refund for return {$return->return_number}",
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                    ]);
                }
            }
            
            // Update return record
            $return->update([
                'status' => 'completed',
                'refund_method' => $request->refund_method,
                'refund_amount' => $request->refund_amount,
                'refund_reference' => $request->refund_reference,
                'refund_completed_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);
            
            // Update invoice status to Return
            $invoice = $return->invoice;
            if ($invoice && $return->return_type === 'full') {
                $invoice->update(['status' => ProformaInvoice::STATUS_RETURN]);
            }
            
            $this->logActivity('Processed Refund', "Processed refund of ₹{$request->refund_amount} for return {$return->return_number}", $return);
            
            // Send notification to customer
            $this->sendNotificationToCustomer(
                $return,
                'Refund Completed',
                "Your refund of ₹{$request->refund_amount} for return {$return->return_number} has been processed successfully."
            );
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Refund processed successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }
    
    /**
     * Export returns data
     */
    public function export(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        $query = ProductReturn::where('vendor_id', $vendor->id)
            ->with(['user', 'vendorCustomer', 'invoice']);
        
        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $returns = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'returns_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($returns) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Return Number',
                'Customer Name',
                'Customer Type',
                'Invoice Number',
                'Return Amount',
                'Return Type',
                'Status',
                'Reason',
                'Created At',
                'Completed At',
            ]);
            
            // Data rows
            foreach ($returns as $return) {
                $customerName = $return->vendorCustomer ? $return->vendorCustomer->name : ($return->user ? $return->user->name : 'N/A');
                $customerType = $return->vendorCustomer ? 'Vendor Customer' : 'User';
                
                fputcsv($file, [
                    $return->return_number,
                    $customerName,
                    $customerType,
                    $return->invoice->invoice_number ?? 'N/A',
                    $return->return_amount,
                    ucfirst($return->return_type),
                    ucfirst(str_replace('_', ' ', $return->status)),
                    $return->reason_category,
                    $return->created_at->format('Y-m-d H:i:s'),
                    $return->refund_completed_at ? $return->refund_completed_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Send notification to customer
     */
    private function sendNotificationToCustomer($return, $title, $message)
    {
        if ($return->vendor_customer_id) {
            // Notification for vendor customer
            Notification::create([
                'vendor_customer_id' => $return->vendor_customer_id,
                'title' => $title,
                'message' => $message,
                'type' => 'return_update',
                'data' => json_encode([
                    'return_id' => $return->id,
                    'return_number' => $return->return_number,
                    'status' => $return->status,
                ]),
            ]);
            
            // TODO: Send push notification if device_token exists
            
        } elseif ($return->user_id) {
            // Notification for regular user
            Notification::create([
                'user_id' => $return->user_id,
                'title' => $title,
                'message' => $message,
                'type' => 'return_update',
                'data' => json_encode([
                    'return_id' => $return->id,
                    'return_number' => $return->return_number,
                    'status' => $return->status,
                ]),
            ]);
            
            // TODO: Send email notification
        }
    }
    
    /**
     * Export returns to Excel
     */
    public function exportExcel(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        if (!$vendor) {
            abort(403, 'Unauthorized access');
        }
        
        $query = ProductReturn::where('vendor_id', $vendor->id)
            ->with(['user', 'vendorCustomer', 'invoice']);
        
        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('customer_type')) {
            if ($request->customer_type === 'vendor_customer') {
                $query->whereNotNull('vendor_customer_id');
            } elseif ($request->customer_type === 'user') {
                $query->whereNotNull('user_id');
            }
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $returns = $query->orderBy('created_at', 'desc')->get();
        
        // Create CSV content
        $filename = 'returns_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($returns) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Return ID',
                'Invoice Number',
                'Customer Name',
                'Customer Type',
                'Return Reason',
                'Return Amount',
                'Status',
                'Requested Date',
                'Reviewed Date',
                'Refund Processed Date',
            ]);
            
            // Add data rows
            foreach ($returns as $return) {
                $customerName = $return->user ? $return->user->name : ($return->vendorCustomer ? $return->vendorCustomer->name : 'N/A');
                $customerType = $return->user ? 'Web' : 'App';
                
                fputcsv($file, [
                    $return->id,
                    $return->invoice->invoice_number ?? 'N/A',
                    $customerName,
                    $customerType,
                    $return->reason_description ?? $return->reason_category,
                    number_format($return->refund_amount, 2),
                    ucfirst(str_replace('_', ' ', $return->status)),
                    $return->created_at->format('Y-m-d H:i:s'),
                    $return->reviewed_at ? $return->reviewed_at->format('Y-m-d H:i:s') : 'N/A',
                    $return->refund_completed_at ? $return->refund_completed_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export returns to PDF
     */
    public function exportPdf(Request $request)
    {
        $vendor = Auth::user()->vendor;
        
        if (!$vendor) {
            abort(403, 'Unauthorized access');
        }
        
        $query = ProductReturn::where('vendor_id', $vendor->id)
            ->with(['user', 'vendorCustomer', 'invoice']);
        
        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('customer_type')) {
            if ($request->customer_type === 'vendor_customer') {
                $query->whereNotNull('vendor_customer_id');
            } elseif ($request->customer_type === 'user') {
                $query->whereNotNull('user_id');
            }
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $returns = $query->orderBy('created_at', 'desc')->get();
        
        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('vendor.returns.pdf', compact('returns', 'vendor'));
        
        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');
        
        // Download the PDF
        return $pdf->download('returns_' . date('Y-m-d_His') . '.pdf');
    }
}
