<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'is_admin_notification',
        'title',
        'body',
        'data',
        'target_type',
        'customer_ids',
        'user_id',
        'user_group_id',
        'scheduled_at',
        'status',
        'error_message',
        'success_count',
        'fail_count',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'customer_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_admin_notification' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Target type constants - Vendor
     */
    const TARGET_ALL = 'all';
    const TARGET_SELECTED = 'selected';
    
    /**
     * Target type constants - Admin
     */
    const TARGET_USER = 'user';
    const TARGET_GROUP = 'group';
    const TARGET_ALL_USERS = 'all_users';

    /**
     * Get the vendor that owns the scheduled notification
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    
    /**
     * Get the user for admin notifications
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the user group for admin notifications
     */
    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class);
    }
    
    /**
     * Get the creator of the notification
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for pending notifications that are due
     */
    public function scopeDue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope for pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    /**
     * Scope for vendor notifications
     */
    public function scopeVendor($query)
    {
        return $query->where('is_admin_notification', false);
    }
    
    /**
     * Scope for admin notifications
     */
    public function scopeAdmin($query)
    {
        return $query->where('is_admin_notification', true);
    }

    /**
     * Check if notification can be edited
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if notification can be cancelled
     */
    public function isCancellable(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_SENT => 'bg-success',
            self::STATUS_FAILED => 'bg-danger',
            self::STATUS_CANCELLED => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Scheduled',
            self::STATUS_SENT => 'Sent',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
    
    /**
     * Get target description
     */
    public function getTargetDescription(): string
    {
        if ($this->is_admin_notification) {
            return match($this->target_type) {
                self::TARGET_USER => 'Single User',
                self::TARGET_GROUP => 'User Group',
                self::TARGET_ALL_USERS => 'All Users',
                default => ucfirst($this->target_type),
            };
        }
        
        return match($this->target_type) {
            self::TARGET_ALL => 'All Customers',
            self::TARGET_SELECTED => 'Selected Customers',
            default => ucfirst($this->target_type),
        };
    }
}
