<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralEarning extends Model
{
    protected $fillable = [
        'referrer_vendor_id',
        'referred_vendor_id',
        'subscription_id',
        'referral_code',
        'subscription_amount',
        'commission_percentage',
        'commission_amount',
        'status',
        'payout_id',
        'approved_at',
        'paid_at',
        'admin_notes',
    ];

    protected $casts = [
        'subscription_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the vendor who referred (earns commission)
     */
    public function referrerVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'referrer_vendor_id');
    }

    /**
     * Get the vendor who was referred (made purchase)
     */
    public function referredVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'referred_vendor_id');
    }

    /**
     * Get the subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    /**
     * Get the payout (if paid)
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(VendorPayout::class, 'payout_id');
    }

    /**
     * Scope for pending earnings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved earnings
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for paid earnings
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Check if earning can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending' && 
               $this->subscription->status === 'active';
    }

    /**
     * Check if earning can be paid
     */
    public function canBePaid(): bool
    {
        return $this->status === 'approved' && 
               $this->payout_id === null;
    }

    /**
     * Approve the earning
     */
    public function approve(?string $notes = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'admin_notes' => $notes,
        ]);

        return true;
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(int $payoutId): bool
    {
        if (!$this->canBePaid()) {
            return false;
        }

        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payout_id' => $payoutId,
        ]);

        return true;
    }
}
