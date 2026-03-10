<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayoutSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'frequency',
        'day',
        'payout_time',
        'scheduled_at',
        'last_run_at',
        'payout_mode',
        'enabled',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_amount' => 'decimal:2',
        'enabled' => 'boolean',
        'scheduled_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    /**
     * Frequency constants
     */
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_BIWEEKLY = 'biweekly';
    const FREQUENCY_MONTHLY = 'monthly';

    /**
     * Get the user who last updated the schedule.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if the schedule should run now
     * Supports both calendar-based (scheduled_at) and frequency-based scheduling
     */
    public function shouldRunNow(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // Calendar-based scheduling (new method)
        if ($this->scheduled_at) {
            return $this->shouldRunByCalendar();
        }

        // Frequency-based scheduling (legacy method)
        return $this->shouldRunToday();
    }

    /**
     * Check if the schedule should run based on calendar date/time
     */
    protected function shouldRunByCalendar(): bool
    {
        $now = now();
        $scheduledAt = Carbon::parse($this->scheduled_at);

        // Check if the scheduled time has passed
        if ($now->lt($scheduledAt)) {
            return false;
        }

        // Check if already run (within 5 minutes of scheduled time to avoid duplicate runs)
        if ($this->last_run_at) {
            $lastRun = Carbon::parse($this->last_run_at);
            
            // If last run was within 5 minutes of the scheduled time, don't run again
            if ($lastRun->diffInMinutes($scheduledAt) <= 5) {
                return false;
            }
        }

        // Check if current time is within 5 minutes of scheduled time
        $diffInMinutes = $now->diffInMinutes($scheduledAt);
        
        return $diffInMinutes <= 5;
    }

    /**
     * Check if the schedule should run today (legacy frequency-based method)
     */
    public function shouldRunToday(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $now = now();
        $currentTime = $now->format('H:i');
        
        // First check if it's the right time (within a 5-minute window)
        if (!$this->isTimeToRun($currentTime)) {
            return false;
        }
        
        switch ($this->frequency) {
            case self::FREQUENCY_WEEKLY:
                // Day 1-7 (Monday-Sunday)
                return (int)$now->format('N') === (int)$this->day;
                
            case self::FREQUENCY_BIWEEKLY:
                // Run on the specified day, but only if it's the 1st or 3rd week
                $weekOfMonth = ceil($now->format('j') / 7);
                return (int)$now->format('N') === (int)$this->day && ($weekOfMonth === 1 || $weekOfMonth === 3);
                
            case self::FREQUENCY_MONTHLY:
                if ($this->day === 'last') {
                    // Last day of the month
                    return $now->format('j') === $now->format('t');
                } else {
                    // Specific day of month (1-31)
                    return (int)$now->format('j') === (int)$this->day;
                }
                
            default:
                return false;
        }
    }
    
    /**
     * Check if it's time to run the payout (within a 5-minute window)
     */
    protected function isTimeToRun(string $currentTime): bool
    {
        if (empty($this->payout_time)) {
            return true; // If no time specified, run at any time
        }
        
        // Parse the scheduled time
        list($scheduledHour, $scheduledMinute) = explode(':', $this->payout_time);
        $scheduledHour = (int)$scheduledHour;
        $scheduledMinute = (int)$scheduledMinute;
        
        // Parse the current time
        list($currentHour, $currentMinute) = explode(':', $currentTime);
        $currentHour = (int)$currentHour;
        $currentMinute = (int)$currentMinute;
        
        // Convert both times to minutes since midnight for easy comparison
        $scheduledTotalMinutes = ($scheduledHour * 60) + $scheduledMinute;
        $currentTotalMinutes = ($currentHour * 60) + $currentMinute;
        
        // Check if current time is within a 5-minute window of the scheduled time
        return abs($currentTotalMinutes - $scheduledTotalMinutes) <= 5;
    }

    /**
     * Mark this schedule as executed
     */
    public function markAsExecuted(): void
    {
        $this->last_run_at = now();
        $this->save();
    }
}