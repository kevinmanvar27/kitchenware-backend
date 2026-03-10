<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Referral extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'amount',
        'referral_code',
        'status',
        'payment_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Generate a unique referral code.
     * 
     * @param string|null $customCode Custom code provided by admin
     */
    public static function generateReferralCode(?string $customCode = null): string
    {
        // If custom code is provided, validate and use it
        if (!empty($customCode)) {
            $code = strtoupper(trim($customCode));
            
            // Check if the custom code already exists
            if (self::where('referral_code', $code)->exists()) {
                throw new \Exception('This referral code already exists. Please use a different code.');
            }
            
            return $code;
        }
        
        // Auto-generate unique code
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Check if the referral is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the referral is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Scope a query to only include active referrals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive referrals.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the payment status badge class.
     */
    public function getPaymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the referral users (users who signed up using this referral code).
     */
    public function referralUsers()
    {
        return $this->hasMany(ReferralUser::class);
    }

    /**
     * Get the count of users who used this referral code.
     */
    public function getReferralUsersCountAttribute(): int
    {
        return $this->referralUsers()->count();
    }

    /**
     * Get the count of users with pending payment.
     */
    public function getPendingUsersCountAttribute(): int
    {
        return $this->referralUsers()->pending()->count();
    }

    /**
     * Get the count of users with paid payment.
     */
    public function getPaidUsersCountAttribute(): int
    {
        return $this->referralUsers()->paid()->count();
    }

    /**
     * Get the total paid amount for this referral.
     */
    public function getTotalPaidAmountAttribute(): float
    {
        return (float) $this->referralUsers()->paid()->sum('payment_amount');
    }

    /**
     * Get the total pending amount for this referral.
     * Pending amount = (number of pending users) * (referral amount per user)
     */
    public function getTotalPendingAmountAttribute(): float
    {
        return (float) ($this->pending_users_count * $this->amount);
    }

    /**
     * Get the total amount (paid + pending) for this referral.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->total_paid_amount + $this->total_pending_amount;
    }
}
