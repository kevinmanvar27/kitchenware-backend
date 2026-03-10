<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'total_days',
        'present_days',
        'absent_days',
        'half_days',
        'leave_days',
        'holiday_days',
        'base_salary',
        'daily_rate',
        'earned_salary',
        'deductions',
        'bonus',
        'net_salary',
        'paid_amount',
        'pending_amount',
        'status',
        'payment_date',
        'payment_method',
        'transaction_id',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'earned_salary' => 'decimal:2',
        'deductions' => 'decimal:2',
        'bonus' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'total_days' => 0,
        'present_days' => 0,
        'absent_days' => 0,
        'half_days' => 0,
        'leave_days' => 0,
        'holiday_days' => 0,
        'base_salary' => 0,
        'daily_rate' => 0,
        'earned_salary' => 0,
        'deductions' => 0,
        'bonus' => 0,
        'net_salary' => 0,
        'paid_amount' => 0,
        'pending_amount' => 0,
        'status' => 'pending',
    ];

    /**
     * Get the user that owns the salary payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who processed this payment.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the month name.
     */
    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    /**
     * Get the period string (e.g., "January 2026").
     */
    public function getPeriodAttribute()
    {
        return $this->month_name . ' ' . $this->year;
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'paid' => 'bg-success',
            'partial' => 'bg-warning',
            'pending' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Scope to filter by month and year.
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Calculate and update salary based on attendance.
     * Properly handles mid-month salary changes by applying the correct rate for each day.
     */
    public function calculateFromAttendance()
    {
        // Ensure we have user_id set
        if (!$this->user_id) {
            return $this;
        }

        // Get attendance for this month - key by date string for proper lookup
        $attendances = Attendance::forUser($this->user_id)
                                 ->forMonth($this->month, $this->year)
                                 ->get()
                                 ->keyBy(function ($item) {
                                     return $item->date->format('Y-m-d');
                                 });
        
        // Get all dates in this month
        $startDate = Carbon::createFromDate($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $today = Carbon::today();
        
        // If current month, only count up to today
        if ($endDate->gt($today)) {
            $endDate = $today;
        }
        
        $this->total_days = $startDate->diffInDays($endDate) + 1;
        
        // Reset attendance counts
        $presentDays = 0;
        $absentDays = 0;
        $halfDays = 0;
        $leaveDays = 0;
        $holidayDays = 0;
        
        // Calculate earned salary day by day
        // This properly handles mid-month salary changes
        $totalEarned = 0;
        $currentDate = $startDate->copy();
        
        // Track salary breakdown for debugging/display
        $salaryBreakdown = [];
        
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $attendance = $attendances->get($dateKey);
            
            if ($attendance) {
                // Count attendance by status
                switch ($attendance->status) {
                    case 'present':
                        $presentDays++;
                        break;
                    case 'absent':
                        $absentDays++;
                        break;
                    case 'half_day':
                        $halfDays++;
                        break;
                    case 'leave':
                        $leaveDays++;
                        break;
                    case 'holiday':
                        $holidayDays++;
                        break;
                }
                
                // Get the salary applicable for this specific date
                // This handles mid-month salary changes correctly
                $salary = Salary::getApplicableSalary($this->user_id, $currentDate);
                
                if ($salary) {
                    $dailyEarning = 0;
                    
                    switch ($attendance->status) {
                        case 'present':
                        case 'leave':
                        case 'holiday':
                            $dailyEarning = $salary->daily_rate;
                            break;
                        case 'half_day':
                            $dailyEarning = $salary->half_day_rate;
                            break;
                        // absent = 0
                    }
                    
                    $totalEarned += $dailyEarning;
                    
                    // Track breakdown by salary rate
                    $salaryId = $salary->id;
                    if (!isset($salaryBreakdown[$salaryId])) {
                        $salaryBreakdown[$salaryId] = [
                            'salary' => $salary,
                            'days' => 0,
                            'half_days' => 0,
                            'earned' => 0,
                            'effective_from' => $salary->effective_from,
                            'effective_to' => $salary->effective_to,
                        ];
                    }
                    
                    if ($attendance->status === 'half_day') {
                        $salaryBreakdown[$salaryId]['half_days']++;
                    } elseif (in_array($attendance->status, ['present', 'leave', 'holiday'])) {
                        $salaryBreakdown[$salaryId]['days']++;
                    }
                    $salaryBreakdown[$salaryId]['earned'] += $dailyEarning;
                }
            }
            
            $currentDate->addDay();
        }
        
        // Update attendance counts
        $this->present_days = $presentDays;
        $this->absent_days = $absentDays;
        $this->half_days = $halfDays;
        $this->leave_days = $leaveDays;
        $this->holiday_days = $holidayDays;
        
        // Get current active salary for base salary reference (display purposes)
        $currentSalary = Salary::where('user_id', $this->user_id)->active()->first();
        
        $this->base_salary = $currentSalary ? $currentSalary->base_salary : 0;
        $this->daily_rate = $currentSalary ? $currentSalary->daily_rate : 0;
        $this->earned_salary = round($totalEarned, 2);
        
        // Preserve existing deductions and bonus if already set
        $deductions = $this->deductions ?? 0;
        $bonus = $this->bonus ?? 0;
        
        $this->net_salary = round($this->earned_salary - $deductions + $bonus, 2);
        
        // Preserve existing paid amount
        $paidAmount = $this->paid_amount ?? 0;
        $this->pending_amount = round(max(0, $this->net_salary - $paidAmount), 2);
        
        // Update status based on payment
        if ($paidAmount >= $this->net_salary && $this->net_salary > 0) {
            $this->status = 'paid';
        } elseif ($paidAmount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }
        
        return $this;
    }

    /**
     * Get detailed salary breakdown for the month.
     * Useful for showing how salary was calculated when there are mid-month changes.
     */
    public function getSalaryBreakdown()
    {
        if (!$this->user_id) {
            return [];
        }

        $attendances = Attendance::forUser($this->user_id)
                                 ->forMonth($this->month, $this->year)
                                 ->get()
                                 ->keyBy(function ($item) {
                                     return $item->date->format('Y-m-d');
                                 });
        
        $startDate = Carbon::createFromDate($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $today = Carbon::today();
        
        if ($endDate->gt($today)) {
            $endDate = $today;
        }
        
        $breakdown = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $attendance = $attendances->get($dateKey);
            
            if ($attendance) {
                $salary = Salary::getApplicableSalary($this->user_id, $currentDate);
                
                if ($salary) {
                    $salaryId = $salary->id;
                    if (!isset($breakdown[$salaryId])) {
                        $breakdown[$salaryId] = [
                            'base_salary' => $salary->base_salary,
                            'daily_rate' => $salary->daily_rate,
                            'half_day_rate' => $salary->half_day_rate,
                            'effective_from' => $salary->effective_from->format('d M Y'),
                            'effective_to' => $salary->effective_to ? $salary->effective_to->format('d M Y') : 'Present',
                            'present_days' => 0,
                            'half_days' => 0,
                            'leave_days' => 0,
                            'holiday_days' => 0,
                            'earned' => 0,
                        ];
                    }
                    
                    $dailyEarning = 0;
                    switch ($attendance->status) {
                        case 'present':
                            $breakdown[$salaryId]['present_days']++;
                            $dailyEarning = $salary->daily_rate;
                            break;
                        case 'half_day':
                            $breakdown[$salaryId]['half_days']++;
                            $dailyEarning = $salary->half_day_rate;
                            break;
                        case 'leave':
                            $breakdown[$salaryId]['leave_days']++;
                            $dailyEarning = $salary->daily_rate;
                            break;
                        case 'holiday':
                            $breakdown[$salaryId]['holiday_days']++;
                            $dailyEarning = $salary->daily_rate;
                            break;
                    }
                    
                    $breakdown[$salaryId]['earned'] += $dailyEarning;
                }
            }
            
            $currentDate->addDay();
        }
        
        return $breakdown;
    }
}
