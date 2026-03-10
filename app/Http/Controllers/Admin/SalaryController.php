<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\SalaryPayment;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class SalaryController extends Controller
{
    use LogsActivity;

    /**
     * Display salary listing for all staff.
     */
    public function index(Request $request)
    {
        // Get staff users with their active salary
        $staffUsers = User::where('user_role', '!=', 'user')
                          ->with(['salaries' => function ($query) {
                              $query->where('is_active', true)->orderBy('effective_from', 'desc');
                          }])
                          ->orderBy('name')
                          ->get();
        
        return view('admin.salary.index', compact('staffUsers'));
    }

    /**
     * Show form for setting/updating user salary.
     */
    public function create(Request $request)
    {
        $userId = $request->get('user_id');
        $user = $userId ? User::findOrFail($userId) : null;
        
        // Get staff users with their active salary
        $staffUsers = User::where('user_role', '!=', 'user')
                          ->with(['salaries' => function ($query) {
                              $query->where('is_active', true)->orderBy('effective_from', 'desc');
                          }])
                          ->orderBy('name')
                          ->get();
        
        // Get salary history for the user
        $salaryHistory = $user ? Salary::where('user_id', $user->id)
                                       ->orderBy('effective_from', 'desc')
                                       ->get() : collect();
        
        return view('admin.salary.create', compact('user', 'staffUsers', 'salaryHistory'));
    }

    /**
     * Store a new salary record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'working_days_per_month' => 'required|integer|min:1|max:31',
            'effective_from' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $user = User::find($request->user_id);
        $salary = null;
        
        DB::transaction(function () use ($request, &$salary) {
            // Deactivate previous active salary and set effective_to date
            $previousSalary = Salary::where('user_id', $request->user_id)
                                    ->where('is_active', true)
                                    ->first();
            
            if ($previousSalary) {
                $effectiveFrom = Carbon::parse($request->effective_from);
                $previousSalary->effective_to = $effectiveFrom->copy()->subDay();
                $previousSalary->is_active = false;
                $previousSalary->save();
            }
            
            // Create new salary record
            $salary = Salary::create([
                'user_id' => $request->user_id,
                'base_salary' => $request->base_salary,
                'working_days_per_month' => $request->working_days_per_month,
                'effective_from' => $request->effective_from,
                'is_active' => true,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);
            
            // Recalculate salary payments for affected months
            $this->recalculateAffectedPayments($request->user_id, Carbon::parse($request->effective_from));
        });
        
        // Log activity
        $this->logAdminActivity(
            'created',
            "Created salary record for {$user->name}: ₹" . number_format($request->base_salary, 2),
            $salary
        );
        
        return redirect()->route('admin.salary.index')->with('success', 'Salary updated successfully.');
    }

    /**
     * Recalculate salary payments for months affected by salary change.
     */
    private function recalculateAffectedPayments($userId, Carbon $effectiveFrom)
    {
        // Get all payments from the effective date onwards
        $payments = SalaryPayment::where('user_id', $userId)
                                 ->where(function ($query) use ($effectiveFrom) {
                                     $query->where('year', '>', $effectiveFrom->year)
                                           ->orWhere(function ($q) use ($effectiveFrom) {
                                               $q->where('year', $effectiveFrom->year)
                                                 ->where('month', '>=', $effectiveFrom->month);
                                           });
                                 })
                                 ->get();
        
        foreach ($payments as $payment) {
            $payment->calculateFromAttendance();
            $payment->save();
        }
    }

    /**
     * Show salary details for a user.
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);
        $activeSalary = Salary::where('user_id', $userId)->where('is_active', true)->first();
        $salaryHistory = Salary::where('user_id', $userId)->orderBy('effective_from', 'desc')->get();
        
        return view('admin.salary.show', compact('user', 'activeSalary', 'salaryHistory'));
    }

    /**
     * Show salary payments/payroll page.
     */
    public function payments(Request $request)
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);
        $refresh = $request->get('refresh', false);
        
        // Get staff users (non-regular users)
        $staffUsers = User::where('user_role', '!=', 'user')->orderBy('name')->get();
        
        $payments = [];
        foreach ($staffUsers as $user) {
            // Find or create payment record for this user/month/year
            $payment = SalaryPayment::firstOrNew([
                'user_id' => $user->id,
                'month' => $month,
                'year' => $year,
            ]);
            
            // Set user_id explicitly for new records
            if (!$payment->exists) {
                $payment->user_id = $user->id;
                $payment->month = $month;
                $payment->year = $year;
            }
            
            // Calculate from attendance if new record or refresh requested
            if (!$payment->exists || $refresh) {
                $payment->calculateFromAttendance();
                $payment->save();
            }
            
            // Attach user for display
            $payment->setRelation('user', $user);
            $payments[] = $payment;
        }
        
        // Calculate totals
        $totals = [
            'total_earned' => collect($payments)->sum('earned_salary'),
            'total_deductions' => collect($payments)->sum('deductions'),
            'total_bonus' => collect($payments)->sum('bonus'),
            'total_net' => collect($payments)->sum('net_salary'),
            'total_paid' => collect($payments)->sum('paid_amount'),
            'total_pending' => collect($payments)->sum('pending_amount'),
        ];
        
        return view('admin.salary.payments', compact('payments', 'month', 'year', 'totals'));
    }

    /**
     * Process salary payment.
     */
    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'transaction_id' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $payment = SalaryPayment::findOrFail($id);
        
        // Store old values for logging
        $oldPaidAmount = $payment->paid_amount;
        $oldStatus = $payment->status;
        
        $payment->paid_amount = $request->paid_amount;
        $payment->payment_method = $request->payment_method;
        $payment->transaction_id = $request->transaction_id;
        $payment->payment_date = $request->payment_date ?? Carbon::today();
        $payment->notes = $request->notes;
        $payment->processed_by = Auth::id();
        
        // Recalculate pending amount and status
        $payment->pending_amount = max(0, $payment->net_salary - $payment->paid_amount);
        
        if ($payment->paid_amount >= $payment->net_salary && $payment->net_salary > 0) {
            $payment->status = 'paid';
        } elseif ($payment->paid_amount > 0) {
            $payment->status = 'partial';
        } else {
            $payment->status = 'pending';
        }
        
        $payment->save();
        
        // Log activity
        $user = User::find($payment->user_id);
        $userName = $user ? $user->name : "User #{$payment->user_id}";
        $monthName = Carbon::create()->month($payment->month)->format('F');
        $this->logAdminActivity(
            'processed_payment',
            "Processed salary payment for {$userName} ({$monthName} {$payment->year}): ₹" . number_format($request->paid_amount, 2) . " - Status: {$payment->status}",
            $payment
        );
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment' => $payment,
            ]);
        }
        
        return redirect()->back()->with('success', 'Payment processed successfully.');
    }

    /**
     * Update deductions and bonus for a payment.
     */
    public function updateAdjustments(Request $request, $id)
    {
        $request->validate([
            'deductions' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $payment = SalaryPayment::findOrFail($id);
        
        // Store old values for logging
        $oldDeductions = $payment->deductions;
        $oldBonus = $payment->bonus;
        
        $payment->deductions = $request->deductions ?? 0;
        $payment->bonus = $request->bonus ?? 0;
        
        if ($request->has('notes')) {
            $payment->notes = $request->notes;
        }
        
        // Recalculate net salary
        $payment->net_salary = round($payment->earned_salary - $payment->deductions + $payment->bonus, 2);
        $payment->pending_amount = round(max(0, $payment->net_salary - $payment->paid_amount), 2);
        
        // Update status
        if ($payment->paid_amount >= $payment->net_salary && $payment->net_salary > 0) {
            $payment->status = 'paid';
        } elseif ($payment->paid_amount > 0) {
            $payment->status = 'partial';
        } else {
            $payment->status = 'pending';
        }
        
        $payment->save();
        
        // Log activity
        $user = User::find($payment->user_id);
        $userName = $user ? $user->name : "User #{$payment->user_id}";
        $monthName = Carbon::create()->month($payment->month)->format('F');
        $changes = [];
        if ($oldDeductions != $payment->deductions) {
            $changes[] = "deductions: ₹" . number_format($oldDeductions, 2) . " → ₹" . number_format($payment->deductions, 2);
        }
        if ($oldBonus != $payment->bonus) {
            $changes[] = "bonus: ₹" . number_format($oldBonus, 2) . " → ₹" . number_format($payment->bonus, 2);
        }
        $changesStr = !empty($changes) ? ' (' . implode(', ', $changes) . ')' : '';
        $this->logAdminActivity(
            'updated',
            "Updated salary adjustments for {$userName} ({$monthName} {$payment->year}){$changesStr}",
            $payment
        );
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Adjustments updated successfully',
                'payment' => $payment,
            ]);
        }
        
        return redirect()->back()->with('success', 'Adjustments updated successfully.');
    }

    /**
     * Recalculate salary for a specific payment.
     */
    public function recalculate($id)
    {
        $payment = SalaryPayment::findOrFail($id);
        $payment->calculateFromAttendance();
        $payment->save();
        
        return redirect()->back()->with('success', 'Salary recalculated successfully.');
    }

    /**
     * Show salary slip for a payment.
     */
    public function slip($id)
    {
        $payment = SalaryPayment::with(['user', 'processedBy'])->findOrFail($id);
        
        // Get attendance details for this month
        $attendances = Attendance::forUser($payment->user_id)
                                 ->forMonth($payment->month, $payment->year)
                                 ->orderBy('date')
                                 ->get();
        
        // Get salary breakdown for mid-month changes
        $salaryBreakdown = $payment->getSalaryBreakdown();
        
        return view('admin.salary.slip', compact('payment', 'attendances', 'salaryBreakdown'));
    }

    /**
     * Download salary slip as PDF.
     */
    public function downloadSlip($id)
    {
        $payment = SalaryPayment::with(['user', 'processedBy'])->findOrFail($id);
        
        // Get attendance details for this month
        $attendances = Attendance::forUser($payment->user_id)
                                 ->forMonth($payment->month, $payment->year)
                                 ->orderBy('date')
                                 ->get();
        
        // Get salary breakdown for mid-month changes
        $salaryBreakdown = $payment->getSalaryBreakdown();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.salary.slip-pdf', compact('payment', 'attendances', 'salaryBreakdown'));
        
        return $pdf->download('salary-slip-' . $payment->user->name . '-' . $payment->period . '.pdf');
    }

    /**
     * Delete a salary record.
     */
    public function destroy($id)
    {
        $salary = Salary::findOrFail($id);
        $userId = $salary->user_id;
        $effectiveFrom = $salary->effective_from;
        
        // Get user info for logging before deletion
        $user = User::find($userId);
        $userName = $user ? $user->name : "User #{$userId}";
        $baseSalary = $salary->base_salary;
        
        // If this was active, activate the previous one
        if ($salary->is_active) {
            $previousSalary = Salary::where('user_id', $salary->user_id)
                                    ->where('id', '!=', $id)
                                    ->orderBy('effective_from', 'desc')
                                    ->first();
            
            if ($previousSalary) {
                $previousSalary->is_active = true;
                $previousSalary->effective_to = null;
                $previousSalary->save();
            }
        }
        
        $salary->delete();
        
        // Recalculate affected payments
        $this->recalculateAffectedPayments($userId, $effectiveFrom);
        
        // Log activity
        $this->logAdminActivity(
            'deleted',
            "Deleted salary record for {$userName}: ₹" . number_format($baseSalary, 2) . " (effective from {$effectiveFrom})"
        );
        
        return redirect()->back()->with('success', 'Salary record deleted successfully.');
    }
}
