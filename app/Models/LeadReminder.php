<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadReminder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',
        'vendor_id',
        'title',
        'description',
        'reminder_at',
        'status',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the lead that owns the reminder.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the vendor that owns the reminder.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Scope a query to only include pending reminders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include due reminders.
     */
    public function scopeDue($query)
    {
        return $query->where('status', 'pending')
                     ->where('reminder_at', '<=', now());
    }

    /**
     * Scope a query to only include upcoming reminders.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'pending')
                     ->where('reminder_at', '>', now());
    }

    /**
     * Check if the reminder is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->reminder_at->isPast();
    }

    /**
     * Get the status badge class for display.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => $this->is_overdue ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis',
            'completed' => 'bg-success-subtle text-success-emphasis',
            'dismissed' => 'bg-secondary-subtle text-secondary-emphasis',
            default => 'bg-secondary-subtle text-secondary-emphasis',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'pending' && $this->is_overdue) {
            return 'Overdue';
        }
        
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'dismissed' => 'Dismissed',
            default => ucfirst($this->status),
        };
    }
}
