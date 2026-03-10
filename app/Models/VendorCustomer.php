<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VendorCustomer extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'user_id',
        'first_invoice_id',
        'name',
        'email',
        'password',
        'mobile_number',
        'address',
        'city',
        'state',
        'postal_code',
        'profile_avatar',
        'device_token',
        'discount_percentage',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user (customer) - for backward compatibility with existing customers.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the first invoice that created this relationship.
     */
    public function firstInvoice()
    {
        return $this->belongsTo(ProformaInvoice::class, 'first_invoice_id');
    }

    /**
     * Get all invoices for this customer from their vendor.
     */
    public function invoices()
    {
        return $this->hasMany(ProformaInvoice::class, 'vendor_customer_id');
    }

    /**
     * Get the shopping cart items for this customer.
     */
    public function cartItems()
    {
        return $this->hasMany(ShoppingCartItem::class, 'vendor_customer_id');
    }

    /**
     * Get the wishlist items for this customer.
     */
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class, 'vendor_customer_id');
    }

    /**
     * Check if customer is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get products available to this customer (only from their vendor).
     */
    public function availableProducts()
    {
        return Product::where('vendor_id', $this->vendor_id)
            ->where('status', 'active');
    }

    /**
     * Get categories available to this customer (only from their vendor).
     */
    public function availableCategories()
    {
        return Category::where('vendor_id', $this->vendor_id);
    }

    /**
     * Calculate discounted price for a product.
     */
    public function getDiscountedPrice($price): float
    {
        if ($this->discount_percentage > 0) {
            return $price - ($price * $this->discount_percentage / 100);
        }
        return $price;
    }

    /**
     * Add a customer to a vendor if not already exists.
     * 
     * @param int $vendorId
     * @param int $userId
     * @param int|null $invoiceId
     * @return VendorCustomer
     */
    public static function addCustomerToVendor($vendorId, $userId, $invoiceId = null)
    {
        return static::firstOrCreate(
            [
                'vendor_id' => $vendorId,
                'user_id' => $userId,
            ],
            [
                'first_invoice_id' => $invoiceId,
            ]
        );
    }

    /**
     * Scope for active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for customers of a specific vendor.
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Update the device token for push notifications.
     *
     * @param string|null $token
     * @return bool
     */
    public function updateDeviceToken(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        
        $this->device_token = $token;
        return $this->save();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     * This is used by Sanctum for token authentication.
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the name of the unique identifier for the customer.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the customer.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the profile avatar URL.
     * 
     * @return string|null
     */
    public function getProfileAvatarUrlAttribute()
    {
        if ($this->profile_avatar) {
            // Encode the filename to handle spaces and special characters
            $encodedFilename = rawurlencode($this->profile_avatar);
            
            // Check in vendor-specific customer folder first
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('customer-avatars/' . $this->vendor_id . '/' . $this->profile_avatar)) {
                return asset('storage/customer-avatars/' . $this->vendor_id . '/' . $encodedFilename);
            }
            // Check in general customer-avatars folder
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('customer-avatars/' . $this->profile_avatar)) {
                return asset('storage/customer-avatars/' . $encodedFilename);
            }
        }
        return null;
    }
}
