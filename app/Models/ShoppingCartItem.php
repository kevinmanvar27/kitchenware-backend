<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ShoppingCartItem extends Model
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
        'session_id',
        'product_id',
        'product_variation_id',
        'quantity',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor customer that owns the cart item.
     */
    public function vendorCustomer()
    {
        return $this->belongsTo(VendorCustomer::class);
    }

    /**
     * Get the product associated with the cart item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variation associated with the cart item.
     */
    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    /**
     * Get the effective product name (with variation if applicable)
     */
    public function getProductNameAttribute()
    {
        if ($this->variation) {
            return $this->variation->display_name;
        }
        return $this->product->name;
    }

    /**
     * Get the effective product image
     */
    public function getProductImageAttribute()
    {
        if ($this->variation && $this->variation->image) {
            return $this->variation->image;
        }
        return $this->product->mainPhoto;
    }

    /**
     * Scope a query to only include cart items for the authenticated user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include cart items for a vendor customer.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $vendorCustomerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForVendorCustomer($query, $vendorCustomerId)
    {
        return $query->where('vendor_customer_id', $vendorCustomerId);
    }

    /**
     * Scope a query to only include cart items for a guest session.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $sessionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Get cart items for the current user or session.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forCurrentUser()
    {
        if (Auth::check()) {
            return static::forUser(Auth::id());
        }

        // For guests, use session ID
        $sessionId = session()->getId();
        return static::forSession($sessionId);
    }

    /**
     * Get the total price for this cart item.
     *
     * @return float
     */
    public function getTotalPriceAttribute()
    {
        return $this->price * $this->quantity;
    }
}