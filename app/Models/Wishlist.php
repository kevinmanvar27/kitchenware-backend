<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'vendor_customer_id',
        'vendor_id',
        'product_id',
    ];

    /**
     * Get the user that owns this wishlist item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor customer that owns this wishlist item.
     */
    public function vendorCustomer()
    {
        return $this->belongsTo(VendorCustomer::class);
    }

    /**
     * Get the vendor associated with this wishlist item.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the product in this wishlist item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query to only include items for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->whereNull('vendor_customer_id');
    }

    /**
     * Scope a query to only include items for a specific vendor customer.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $vendorCustomerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForVendorCustomer($query, $vendorCustomerId)
    {
        return $query->where('vendor_customer_id', $vendorCustomerId)->whereNull('user_id');
    }

    /**
     * Scope a query to only include items for a specific vendor.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $vendorId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Get the owner of this wishlist item (either User or VendorCustomer).
     *
     * @return User|VendorCustomer|null
     */
    public function getOwner()
    {
        if ($this->user_id) {
            return $this->user;
        }
        
        if ($this->vendor_customer_id) {
            return $this->vendorCustomer;
        }
        
        return null;
    }

    /**
     * Check if this wishlist item belongs to a regular user.
     *
     * @return bool
     */
    public function isUserWishlist(): bool
    {
        return $this->user_id !== null && $this->vendor_customer_id === null;
    }

    /**
     * Check if this wishlist item belongs to a vendor customer.
     *
     * @return bool
     */
    public function isVendorCustomerWishlist(): bool
    {
        return $this->vendor_customer_id !== null && $this->user_id === null;
    }
}
