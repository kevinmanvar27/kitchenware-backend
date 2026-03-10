<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorWallet extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'vendor_wallets';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendor_id',
        'total_earned',
        'total_paid',
        'pending_amount',
        'hold_amount',
        'status',
        'last_payout_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_earned' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'hold_amount' => 'decimal:2',
        'last_payout_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_HOLD = 'hold';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Get the vendor that owns the wallet.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get payable amount (pending - hold)
     */
    public function getPayableAmountAttribute()
    {
        return max(0, $this->pending_amount - $this->hold_amount);
    }

    /**
     * Check if wallet is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if wallet is on hold
     */
    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_HOLD;
    }

    /**
     * Add earnings to wallet
     */
    public function addEarning(float $amount): void
    {
        $this->increment('total_earned', $amount);
        $this->increment('pending_amount', $amount);
    }

    /**
     * Record payout from wallet
     */
    public function recordPayout(float $amount): void
    {
        $this->increment('total_paid', $amount);
        $this->decrement('pending_amount', $amount);
        $this->update(['last_payout_at' => now()]);
    }

    /**
     * Scope for active wallets
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for wallets with pending payments
     */
    public function scopeWithPendingPayments($query)
    {
        return $query->where('pending_amount', '>', 0);
    }
}
