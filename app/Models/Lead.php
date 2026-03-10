<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_number',
        'note',
        'status',
        'vendor_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vendor that owns the lead.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the reminders for the lead.
     */
    public function reminders()
    {
        return $this->hasMany(LeadReminder::class);
    }

    /**
     * Get the pending reminders for the lead.
     */
    public function pendingReminders()
    {
        return $this->hasMany(LeadReminder::class)->where('status', 'pending');
    }

    /**
     * Get the next reminder for the lead.
     */
    public function nextReminder()
    {
        return $this->hasOne(LeadReminder::class)
                    ->where('status', 'pending')
                    ->orderBy('reminder_at', 'asc');
    }

    /**
     * Get the status label with proper formatting.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'new' => 'New',
            'contacted' => 'Contacted',
            'followup' => 'Follow Up',
            'qualified' => 'Qualified',
            'converted' => 'Converted',
            'lost' => 'Lost',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the status badge class for display.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'new' => 'bg-info-subtle text-info-emphasis',
            'contacted' => 'bg-primary-subtle text-primary-emphasis',
            'followup' => 'bg-warning-subtle text-warning-emphasis',
            'qualified' => 'bg-success-subtle text-success-emphasis',
            'converted' => 'bg-success-subtle text-success-emphasis',
            'lost' => 'bg-danger-subtle text-danger-emphasis',
            default => 'bg-secondary-subtle text-secondary-emphasis',
        };
    }
}
