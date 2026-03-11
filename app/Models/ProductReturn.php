<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReturn extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'user_id',
        'vendor_customer_id',
        'proforma_invoice_id',
        'return_number',
        'return_items',
        'return_amount',
        'return_type',
        'reason_category',
        'reason_description',
        'images',
        'status',
        'vendor_notes',
        'reviewed_by',
        'reviewed_at',
        'refund_method',
        'refund_status',
        'refund_reference',
        'refund_amount',
        'refund_completed_at',
        'pickup_address',
        'pickup_contact',
        'pickup_scheduled_at',
        'device_type',
        'app_version',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'return_items' => 'array',
        'images' => 'array',
        'return_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'refund_completed_at' => 'datetime',
        'pickup_scheduled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vendor that owns the return.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user that requested the return (frontend users).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor customer that requested the return (app users).
     */
    public function vendorCustomer()
    {
        return $this->belongsTo(VendorCustomer::class);
    }

    /**
     * Get the invoice associated with the return.
     */
    public function invoice()
    {
        return $this->belongsTo(ProformaInvoice::class, 'proforma_invoice_id');
    }

    /**
     * Get the user who reviewed the return.
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the customer name (works for both user types).
     */
    public function getCustomerNameAttribute()
    {
        if ($this->user_id) {
            return $this->user->name ?? 'Unknown User';
        }
        if ($this->vendor_customer_id) {
            return $this->vendorCustomer->name ?? 'Unknown Customer';
        }
        return 'Guest';
    }

    /**
     * Get the customer email (works for both user types).
     */
    public function getCustomerEmailAttribute()
    {
        if ($this->user_id) {
            return $this->user->email ?? null;
        }
        if ($this->vendor_customer_id) {
            return $this->vendorCustomer->email ?? null;
        }
        return null;
    }

    /**
     * Get the customer mobile (works for both user types).
     */
    public function getCustomerMobileAttribute()
    {
        if ($this->user_id) {
            return $this->user->mobile_number ?? null;
        }
        if ($this->vendor_customer_id) {
            return $this->vendorCustomer->mobile_number ?? null;
        }
        return null;
    }

    /**
     * Check if return is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if return is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if return is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if return is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if return can be cancelled.
     */
    public function canBeCancelled()
    {
        return $this->status === 'pending';
    }

    /**
     * Scope for pending returns.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved returns.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for completed returns.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for returns by vendor.
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope for returns by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for returns by vendor customer.
     */
    public function scopeForVendorCustomer($query, $vendorCustomerId)
    {
        return $query->where('vendor_customer_id', $vendorCustomerId);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'under_review' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'pickup_scheduled' => 'primary',
            'picked_up' => 'primary',
            'received' => 'info',
            'inspected' => 'info',
            'refund_processing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'pickup_scheduled' => 'Pickup Scheduled',
            'picked_up' => 'Picked Up',
            'received' => 'Received',
            'inspected' => 'Inspected',
            'refund_processing' => 'Refund Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
