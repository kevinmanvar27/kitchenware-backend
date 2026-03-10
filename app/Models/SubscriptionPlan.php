<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle', // monthly, quarterly, yearly
        'duration_days',
        'features', // JSON array of features
        'max_products',
        'max_vendors',
        'max_customers',
        'max_invoices_per_month',
        'storage_limit_mb',
        'is_active',
        'is_featured',
        'sort_order',
        'trial_days',
        'discount_percentage',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'max_products' => 'integer',
        'max_vendors' => 'integer',
        'max_customers' => 'integer',
        'max_invoices_per_month' => 'integer',
        'storage_limit_mb' => 'integer',
        'duration_days' => 'integer',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
        'discount_percentage' => 'decimal:2',
    ];

    /**
     * Get the formatted price with currency
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Get billing cycle label
     */
    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            'lifetime' => 'Lifetime',
            default => ucfirst($this->billing_cycle),
        };
    }

    /**
     * Get the discounted price if discount is available
     */
    public function getDiscountedPriceAttribute(): float
    {
        if ($this->discount_percentage > 0) {
            return $this->price - ($this->price * $this->discount_percentage / 100);
        }
        return $this->price;
    }

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get featured plans
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get users subscribed to this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    /**
     * Check if plan has unlimited products
     */
    public function hasUnlimitedProducts(): bool
    {
        return $this->max_products === -1 || $this->max_products === null;
    }

    /**
     * Check if plan has unlimited vendors
     */
    public function hasUnlimitedVendors(): bool
    {
        return $this->max_vendors === -1 || $this->max_vendors === null;
    }

    /**
     * Check if plan has unlimited customers
     */
    public function hasUnlimitedCustomers(): bool
    {
        return $this->max_customers === -1 || $this->max_customers === null;
    }
}
