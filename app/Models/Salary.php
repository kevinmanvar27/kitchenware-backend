<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vendor_id',
        'base_salary',
        'daily_rate',
        'half_day_rate',
        'working_days_per_month',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'half_day_rate' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate daily and half-day rates when saving
        static::saving(function ($salary) {
            if ($salary->base_salary && $salary->working_days_per_month) {
                $salary->daily_rate = round($salary->base_salary / $salary->working_days_per_month, 2);
                $salary->half_day_rate = round($salary->daily_rate / 2, 2);
            }
        });
    }

    /**
     * Get the user that owns the salary.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the salary.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user who created this salary record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this salary record.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get active salaries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get salary for a specific date.
     * Finds salary where the date falls within effective_from and effective_to range.
     */
    public function scopeForDate($query, $date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        
        return $query->where('effective_from', '<=', $dateStr)
                     ->where(function ($q) use ($dateStr) {
                         $q->whereNull('effective_to')
                           ->orWhere('effective_to', '>=', $dateStr);
                     });
    }

    /**
     * Get the applicable salary for a user on a specific date.
     * This is crucial for calculating salary when there are mid-month changes.
     * 
     * @param int $userId
     * @param Carbon|string $date
     * @return Salary|null
     */
    public static function getApplicableSalary($userId, $date)
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : $date;
        
        return self::where('user_id', $userId)
                   ->where('effective_from', '<=', $dateStr)
                   ->where(function ($q) use ($dateStr) {
                       $q->whereNull('effective_to')
                         ->orWhere('effective_to', '>=', $dateStr);
                   })
                   ->orderBy('effective_from', 'desc')
                   ->first();
    }

    /**
     * Get all salaries applicable within a date range for a user.
     * Useful for getting all salary records that apply to a month.
     * 
     * @param int $userId
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSalariesInRange($userId, $startDate, $endDate)
    {
        $startStr = $startDate instanceof Carbon ? $startDate->format('Y-m-d') : $startDate;
        $endStr = $endDate instanceof Carbon ? $endDate->format('Y-m-d') : $endDate;
        
        return self::where('user_id', $userId)
                   ->where(function ($query) use ($startStr, $endStr) {
                       // Salary started before or during the range
                       $query->where('effective_from', '<=', $endStr)
                             ->where(function ($q) use ($startStr) {
                                 // And either has no end date or ends after range start
                                 $q->whereNull('effective_to')
                                   ->orWhere('effective_to', '>=', $startStr);
                             });
                   })
                   ->orderBy('effective_from', 'asc')
                   ->get();
    }

    /**
     * Calculate salary for given attendance data.
     */
    public function calculateSalaryForAttendance($presentDays, $halfDays, $leaveDays = 0, $holidayDays = 0)
    {
        // Full days: present + leave + holiday
        $fullDaysPaid = $presentDays + $leaveDays + $holidayDays;
        
        // Calculate earned salary
        $fullDaySalary = $fullDaysPaid * $this->daily_rate;
        $halfDaySalary = $halfDays * $this->half_day_rate;
        
        return round($fullDaySalary + $halfDaySalary, 2);
    }

    /**
     * Get formatted salary display string.
     */
    public function getFormattedSalaryAttribute()
    {
        return '₹' . number_format($this->base_salary, 2);
    }

    /**
     * Get formatted daily rate display string.
     */
    public function getFormattedDailyRateAttribute()
    {
        return '₹' . number_format($this->daily_rate, 2);
    }

    /**
     * Get formatted half day rate display string.
     */
    public function getFormattedHalfDayRateAttribute()
    {
        return '₹' . number_format($this->half_day_rate, 2);
    }
}
