<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProformaInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class PendingBillController extends Controller
{
    use LogsActivity;
    
    /**
     * Display a listing of pending bills.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $userId = $request->get('user_id');
        $paymentStatus = $request->get('payment_status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query
        $query = ProformaInvoice::with('user')
            ->where('status', '!=', 'Return'); // Exclude returned invoices

        // Apply filters
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Filter by payment status - default to showing only pending bills (unpaid and partial)
        if ($paymentStatus) {
            if ($paymentStatus === 'all') {
                // Show all bills regardless of payment status
                // No additional filter needed
            } else {
                $query->where('payment_status', $paymentStatus);
            }
        } else {
            // By default, show only bills with pending amounts (unpaid or partial)
            $query->whereIn('payment_status', ['unpaid', 'partial']);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary statistics
        $totalBills = $invoices->count();
        $totalAmount = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $totalPending = $totalAmount - $totalPaid;

        // Get unpaid and partial bills count
        $unpaidBills = $invoices->where('payment_status', 'unpaid')->count();
        $partialBills = $invoices->where('payment_status', 'partial')->count();
        $paidBills = $invoices->where('payment_status', 'paid')->count();

        // Get all users for filter dropdown
        $users = User::where('user_role', 'user')->orderBy('name')->get();

        return view('admin.pending-bills.index', compact(
            'invoices',
            'totalBills',
            'totalAmount',
            'totalPaid',
            'totalPending',
            'unpaidBills',
            'partialBills',
            'paidBills',
            'users',
            'userId',
            'paymentStatus',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show pending bills for a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\View\View
     */
    public function userBills($userId)
    {
        $user = User::findOrFail($userId);
        
        $invoices = ProformaInvoice::with('user')
            ->where('user_id', $userId)
            ->where('status', '!=', 'Return')
            ->whereIn('payment_status', ['unpaid', 'partial']) // Show only pending bills
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate user-specific statistics
        $totalAmount = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $totalPending = $totalAmount - $totalPaid;

        // Get customer data from invoice_data if user is deleted/anonymized
        $customerData = null;
        if ($user->name === 'Deleted User' && $invoices->isNotEmpty()) {
            // Try to get customer data from the first invoice that has it
            foreach ($invoices as $invoice) {
                $invoiceData = is_array($invoice->invoice_data) ? $invoice->invoice_data : json_decode($invoice->invoice_data, true);
                if (isset($invoiceData['customer']) && is_array($invoiceData['customer'])) {
                    $customerData = $invoiceData['customer'];
                    break;
                }
            }
        }

        return view('admin.pending-bills.user-bills', compact(
            'user',
            'invoices',
            'totalAmount',
            'totalPaid',
            'totalPending',
            'customerData'
        ));
    }

    /**
     * Update payment for an invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePayment(Request $request, $id)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $invoice = ProformaInvoice::findOrFail($id);
        $oldPaidAmount = $invoice->paid_amount;
        $paidAmount = floatval($request->paid_amount);
        $totalAmount = floatval($invoice->total_amount);

        // Validate paid amount doesn't exceed total
        if ($paidAmount > $totalAmount) {
            return back()->with('error', 'Paid amount cannot exceed total amount.');
        }

        // Update paid amount
        $invoice->paid_amount = $paidAmount;

        // Update payment status
        if ($paidAmount <= 0) {
            $invoice->payment_status = 'unpaid';
        } elseif ($paidAmount >= $totalAmount) {
            $invoice->payment_status = 'paid';
        } else {
            $invoice->payment_status = 'partial';
        }

        $invoice->save();

        // Log payment update
        $this->logAdminActivity('updated_payment', "Updated payment for invoice #{$invoice->invoice_number}: ₹" . number_format($oldPaidAmount, 2) . " → ₹" . number_format($paidAmount, 2) . " (Status: {$invoice->payment_status})", $invoice);

        return back()->with('success', 'Payment updated successfully.');
    }

    /**
     * Add payment to an invoice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_note' => 'nullable|string|max:500',
        ]);

        $invoice = ProformaInvoice::findOrFail($id);
        $addAmount = floatval($request->amount);
        $currentPaid = floatval($invoice->paid_amount);
        $totalAmount = floatval($invoice->total_amount);
        $pendingAmount = $totalAmount - $currentPaid;

        // Validate amount doesn't exceed pending
        if ($addAmount > $pendingAmount) {
            return back()->with('error', 'Payment amount cannot exceed pending amount (₹' . number_format($pendingAmount, 2) . ').');
        }

        // Update paid amount
        $newPaidAmount = $currentPaid + $addAmount;
        $invoice->paid_amount = $newPaidAmount;

        // Update payment status
        if ($newPaidAmount >= $totalAmount) {
            $invoice->payment_status = 'paid';
        } else {
            $invoice->payment_status = 'partial';
        }

        // Update payment method and note
        $invoice->payment_method = $request->payment_method;
        if ($request->payment_note) {
            // Append note to existing notes if any
            $existingNote = $invoice->payment_note ?? '';
            $timestamp = now()->format('d M Y H:i');
            $newNote = "[{$timestamp}] Payment of ₹" . number_format($addAmount, 2) . " via {$request->payment_method}: {$request->payment_note}";
            $invoice->payment_note = $existingNote ? $existingNote . "\n" . $newNote : $newNote;
        }

        $invoice->save();

        // Log payment addition
        $logMessage = "Added payment of ₹" . number_format($addAmount, 2) . " via {$request->payment_method} to invoice #{$invoice->invoice_number} (New total paid: ₹" . number_format($newPaidAmount, 2) . ", Status: {$invoice->payment_status})";
        $this->logAdminActivity('added_payment', $logMessage, $invoice);

        return back()->with('success', 'Payment of ₹' . number_format($addAmount, 2) . ' added successfully via ' . ucfirst($request->payment_method) . '.');
    }

    /**
     * Get summary by user (for reports).
     *
     * @return \Illuminate\View\View
     */
    public function userSummary()
    {
        $userSummary = ProformaInvoice::select(
                'user_id',
                DB::raw('COUNT(*) as total_bills'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('SUM(total_amount - paid_amount) as total_pending')
            )
            ->with('user')
            ->where('status', '!=', 'Return')
            ->groupBy('user_id')
            ->having('total_pending', '>', 0)
            ->orderBy('total_pending', 'desc')
            ->get();

        // Overall totals
        $overallTotal = $userSummary->sum('total_amount');
        $overallPaid = $userSummary->sum('total_paid');
        $overallPending = $userSummary->sum('total_pending');

        return view('admin.pending-bills.user-summary', compact(
            'userSummary',
            'overallTotal',
            'overallPaid',
            'overallPending'
        ));
    }
}
