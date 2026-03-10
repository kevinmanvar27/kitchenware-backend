<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'per_user_limit' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vendor that owns this coupon
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the usage records for this coupon
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Scope to get only active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only valid (not expired) coupons
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', $now);
        });
    }

    /**
     * Scope to get coupons that haven't exceeded usage limit
     */
    public function scopeNotExhausted($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereColumn('usage_count', '<', 'usage_limit');
        });
    }

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Check valid_from
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        // Check valid_until
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if a specific user can use this coupon (returns boolean)
     */
    public function canBeUsedBy(?User $user): bool
    {
        // Check per-user limit if user is authenticated
        if ($user && $this->per_user_limit > 0) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->per_user_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a specific user can use this coupon (returns array with message)
     */
    public function canBeUsedByWithMessage(?User $user): array
    {
        if (!$this->isValid()) {
            if (!$this->is_active) {
                return ['can_use' => false, 'message' => 'This coupon is no longer active.'];
            }
            if ($this->valid_until && Carbon::now()->gt($this->valid_until)) {
                return ['can_use' => false, 'message' => 'This coupon has expired.'];
            }
            if ($this->valid_from && Carbon::now()->lt($this->valid_from)) {
                return ['can_use' => false, 'message' => 'This coupon is not yet valid.'];
            }
            if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
                return ['can_use' => false, 'message' => 'This coupon has reached its usage limit.'];
            }
            return ['can_use' => false, 'message' => 'This coupon is not valid.'];
        }

        // Check per-user limit if user is authenticated
        if ($user && $this->per_user_limit > 0) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->per_user_limit) {
                return ['can_use' => false, 'message' => 'You have already used this coupon the maximum number of times.'];
            }
        }

        return ['can_use' => true, 'message' => 'Coupon is valid.'];
    }

    /**
     * Calculate discount amount for a given cart total
     */
    public function calculateDiscount(float $cartTotal): float
    {
        if ($cartTotal < $this->min_order_amount) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = ($cartTotal * $this->discount_value) / 100;
            
            // Apply max discount cap if set
            if ($this->max_discount_amount !== null && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        } else {
            // Fixed discount
            $discount = $this->discount_value;
        }

        // Discount cannot exceed cart total
        return min($discount, $cartTotal);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Record usage by a user (or guest)
     */
    public function recordUsage(?User $user = null, float $discountApplied = 0, ?int $invoiceId = null): CouponUsage
    {
        $this->incrementUsage();

        return $this->usages()->create([
            'user_id' => $user ? $user->id : null,
            'proforma_invoice_id' => $invoiceId,
            'discount_applied' => $discountApplied,
        ]);
    }

    /**
     * Get formatted discount display
     */
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value . '%';
        }
        return '₹' . number_format($this->discount_value, 2);
    }

    /**
     * Get status badge
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return 'scheduled';
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return 'expired';
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return 'exhausted';
        }

        return 'active';
    }
}
