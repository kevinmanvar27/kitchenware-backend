<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionReminder extends Model
{
    protected $fillable = [
        'vendor_id',
        'user_id',
        'subscription_id',
        'reminder_type',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Reminder types constants
    const TYPE_7_DAYS = '7_days';
    const TYPE_3_DAYS = '3_days';
    const TYPE_1_DAY = '1_day';
    const TYPE_EXPIRED = 'expired';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /**
     * Get the vendor that owns the reminder.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user that owns the reminder.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that owns the reminder.
     */
    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    /**
     * Scope a query to only include pending reminders.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include sent reminders.
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Mark reminder as sent.
     */
    public function markAsSent()
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark reminder as failed.
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get reminder type label.
     */
    public function getReminderTypeLabel(): string
    {
        return match($this->reminder_type) {
            self::TYPE_7_DAYS => '7 Days Before Expiry',
            self::TYPE_3_DAYS => '3 Days Before Expiry',
            self::TYPE_1_DAY => '1 Day Before Expiry',
            self::TYPE_EXPIRED => 'Subscription Expired',
            default => 'Unknown',
        };
    }
}
