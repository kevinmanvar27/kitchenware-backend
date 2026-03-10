<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'user_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'working_hours',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
        'working_hours' => 'decimal:2',
    ];

    /**
     * Get the vendor that owns the attendance record.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user that owns the attendance record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who marked this attendance.
     */
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scope to filter by month and year.
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        $startStr = $startDate instanceof Carbon ? $startDate->format('Y-m-d') : $startDate;
        $endStr = $endDate instanceof Carbon ? $endDate->format('Y-m-d') : $endDate;
        
        return $query->whereBetween('date', [$startStr, $endStr]);
    }

    /**
     * Scope to filter by specific date.
     */
    public function scopeForDate($query, $date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        return $query->whereDate('date', $dateStr);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'present' => 'bg-success',
            'absent' => 'bg-danger',
            'half_day' => 'bg-warning',
            'leave' => 'bg-info',
            'holiday' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'half_day' => 'Half Day',
            'leave' => 'Leave',
            'holiday' => 'Holiday',
            default => ucfirst($this->status),
        };
    }

    /**
     * Calculate working hours from check-in and check-out.
     */
    public function calculateWorkingHours()
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
            return round($checkOut->diffInMinutes($checkIn) / 60, 2);
        }
        return null;
    }

    /**
     * Get the applicable salary rate for this attendance date.
     */
    public function getApplicableSalary()
    {
        return Salary::getApplicableSalary($this->user_id, $this->date);
    }

    /**
     * Get the earned amount for this attendance day.
     */
    public function getEarnedAmountAttribute()
    {
        $salary = $this->getApplicableSalary();
        
        if (!$salary) {
            return 0;
        }

        return match($this->status) {
            'present', 'leave', 'holiday' => $salary->daily_rate,
            'half_day' => $salary->half_day_rate,
            default => 0,
        };
    }

    /**
     * Get attendance summary for a user for a specific month.
     */
    public static function getMonthlySummary($userId, $month, $year)
    {
        $attendances = self::forUser($userId)->forMonth($month, $year)->get();
        
        return [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'holiday' => $attendances->where('status', 'holiday')->count(),
            'total' => $attendances->count(),
        ];
    }
}
