<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'store_description',
        'store_logo',
        'store_banner',
        'banner_image_url',
        'banner_redirect_url',
        'business_email',
        'business_phone',
        'referral_code',
        'business_address',
        'city',
        'state',
        'country',
        'postal_code',
        'gst_number',
        'pan_number',
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_account_holder_name',
        'commission_rate',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'is_featured',
        'priority',
        'social_links',
        'store_settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'commission_rate' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_featured' => 'boolean',
        'social_links' => 'array',
        'store_settings' => 'array',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->store_slug)) {
                $vendor->store_slug = Str::slug($vendor->store_name);
            }
            
            // Ensure unique slug
            $originalSlug = $vendor->store_slug;
            $count = 1;
            while (static::where('store_slug', $vendor->store_slug)->exists()) {
                $vendor->store_slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            // Generate referral code: VendorName-Last4Digits
            if (empty($vendor->referral_code)) {
                $vendor->referral_code = static::generateReferralCode($vendor);
            }
        });

        static::created(function ($vendor) {
            // Create a referral entry in the referrals table for this vendor
            // so it appears in the admin referrals page
            try {
                // Check if referral entry already exists to prevent duplicates
                $existingReferral = \App\Models\Referral::where('referral_code', $vendor->referral_code)->first();
                
                if (!$existingReferral) {
                    \App\Models\Referral::create([
                        'name' => $vendor->store_name,
                        'phone_number' => $vendor->business_phone,
                        'amount' => 0, // Default amount, admin can update later
                        'referral_code' => $vendor->referral_code,
                        'vendor_id' => $vendor->id, // Link to vendor for proper tracking
                        'status' => 'active',
                        'payment_status' => 'pending',
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail vendor creation
                \Log::error('Failed to create referral entry for vendor: ' . $e->getMessage());
            }
        });
    }

    /**
     * Get the user that owns the vendor.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate unique referral code for vendor
     * Format: STORENAME-XXXX (last 4 digits of phone)
     */
    public static function generateReferralCode($vendor): string
    {
        // Get store name (clean and uppercase)
        $storeName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $vendor->store_name));
        $storeName = substr($storeName, 0, 10); // Limit to 10 characters
        
        // Get last 4 digits of phone
        $phone = preg_replace('/[^0-9]/', '', $vendor->business_phone ?? '0000');
        $lastFourDigits = substr($phone, -4);
        
        // Create base code
        $baseCode = $storeName . '-' . $lastFourDigits;
        
        // Ensure uniqueness
        $code = $baseCode;
        $counter = 1;
        while (static::where('referral_code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Get the referral entry for this vendor
     */
    public function referral()
    {
        return $this->hasOne(\App\Models\Referral::class, 'referral_code', 'referral_code');
    }

    /**
     * Get vendors who used this vendor's referral code
     */
    public function referredVendors()
    {
        return $this->hasMany(UserSubscription::class, 'referral_code', 'referral_code')
            ->whereNotNull('referral_code')
            ->with('vendor');
    }

    /**
     * Get the user who approved the vendor.
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the products for the vendor.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the attributes for the vendor.
     */
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Get the categories for the vendor.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the staff members for the vendor.
     */
    public function staff()
    {
        return $this->hasMany(VendorStaff::class);
    }

    /**
     * Get the permissions for the vendor.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'vendor_permissions');
    }

    /**
     * Check if vendor is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if vendor is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if vendor is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if vendor is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if vendor has a specific permission.
     */
    public function hasPermission($permission): bool
    {
        // Check if permission exists in vendor_permissions
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if vendor has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->whereIn('name', $permissions)->exists();
    }

    /**
     * Get all subscriptions for the vendor.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'vendor_id');
    }

    /**
     * Get the active subscription for the vendor (as relationship).
     */
    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class, 'vendor_id')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->latest();
    }
    
    /**
     * Get the active subscription instance (helper method).
     */
    public function getActiveSubscription()
    {
        return $this->activeSubscription;
    }

    /**
     * Check if vendor has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        $subscription = $this->activeSubscription;
        
        if (!$subscription) {
            return false;
        }
        
        return $subscription->status === 'active' && 
               ($subscription->ends_at === null || $subscription->ends_at->isFuture());
    }

    /**
     * Get the current subscription plan.
     */
    public function currentPlan()
    {
        $subscription = $this->activeSubscription;
        return $subscription ? $subscription->plan : null;
    }

    /**
     * Get subscription status (active, expired, trial, none).
     */
    public function getSubscriptionStatus(): string
    {
        $subscription = $this->activeSubscription;
        
        if (!$subscription) {
            return 'none';
        }
        
        if ($subscription->status === 'active') {
            // Check if in trial period
            if ($subscription->trial_ends_at && now()->lt($subscription->trial_ends_at)) {
                return 'trial';
            }
            
            // Check if expired
            if ($subscription->ends_at && now()->gt($subscription->ends_at)) {
                return 'expired';
            }
            
            return 'active';
        }
        
        return $subscription->status;
    }

    /**
     * Get days remaining in subscription.
     */
    public function getSubscriptionDaysRemaining(): ?int
    {
        $subscription = $this->activeSubscription;
        
        if (!$subscription || !$subscription->ends_at) {
            return null;
        }
        
        $daysRemaining = now()->diffInDays($subscription->ends_at, false);
        
        return $daysRemaining > 0 ? (int) $daysRemaining : 0;
    }

    /**
     * Check if subscription is expiring soon (within 7 days).
     */
    public function isSubscriptionExpiringSoon(): bool
    {
        $daysRemaining = $this->getSubscriptionDaysRemaining();
        
        return $daysRemaining !== null && $daysRemaining <= 7 && $daysRemaining > 0;
    }

    /**
     * Check if vendor can access a specific feature.
     */
    public function canAccessFeature(string $feature): bool
    {
        $subscription = $this->activeSubscription;
        
        if (!$subscription) {
            return false;
        }
        
        $plan = $subscription->plan;
        
        if (!$plan || !$plan->features) {
            return false;
        }
        
        $features = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;
        
        return isset($features[$feature]) && $features[$feature] === true;
    }

    /**
     * Get the store logo URL.
     */
    public function getStoreLogoUrlAttribute()
    {
        if ($this->store_logo) {
            // URL-encode the filename to handle spaces and special characters
            $encodedFilename = rawurlencode($this->store_logo);
            
            // Check in vendor root folder
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $this->store_logo)) {
                return asset('storage/vendor/' . $encodedFilename);
            }
            // Check in vendor-specific subfolder
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $this->id . '/' . $this->store_logo)) {
                return asset('storage/vendor/' . $this->id . '/' . $encodedFilename);
            }
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->store_name) . '&background=0D8ABC&color=fff&size=200';
    }

    /**
     * Get the store banner URL.
     */
    public function getStoreBannerUrlAttribute()
    {
        // Prioritize external banner URL if provided
        if (!empty($this->banner_image_url)) {
            return $this->banner_image_url;
        }
        
        // Fallback to uploaded banner file
        if ($this->store_banner) {
            // URL-encode the filename to handle spaces and special characters
            $encodedFilename = rawurlencode($this->store_banner);
            
            // Check in vendor root folder
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $this->store_banner)) {
                return asset('storage/vendor/' . $encodedFilename);
            }
            // Check in vendor-specific subfolder
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $this->id . '/' . $this->store_banner)) {
                return asset('storage/vendor/' . $this->id . '/' . $encodedFilename);
            }
        }
        
        return null;
    }

    /**
     * Scope for approved vendors.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for pending vendors.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for featured vendors.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get total revenue for the vendor.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->products()
            ->join('proforma_invoices', function ($join) {
                $join->whereJsonContains('proforma_invoices.invoice_data->cart_items', ['vendor_id' => $this->id]);
            })
            ->where('proforma_invoices.status', 'delivered')
            ->sum('proforma_invoices.total_amount');
    }

    /**
     * Get total products count.
     */
    public function getTotalProductsAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get all customers of this vendor (users who have sent invoices).
     */
    public function customers()
    {
        return $this->belongsToMany(User::class, 'vendor_customers', 'vendor_id', 'user_id')
            ->withTimestamps()
            ->withPivot('first_invoice_id');
    }

    /**
     * Get the vendor_customers pivot records.
     */
    public function vendorCustomers()
    {
        return $this->hasMany(VendorCustomer::class);
    }

    /**
     * Get total customers count.
     */
    public function getTotalCustomersAttribute()
    {
        return $this->customers()->count();
    }

    /**
     * Get the followers for the vendor.
     */
    public function followers()
    {
        return $this->hasMany(VendorFollower::class);
    }

    /**
     * Get the users who follow this vendor.
     */
    public function followingUsers()
    {
        return $this->belongsToMany(User::class, 'vendor_followers', 'vendor_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get the payouts for the vendor.
     */
    public function payouts()
    {
        return $this->hasMany(VendorPayout::class);
    }

    /**
     * Get the wallet for the vendor.
     */
    public function wallet()
    {
        return $this->hasOne(VendorWallet::class);
    }

    /**
     * Get or create wallet for the vendor.
     */
    public function getOrCreateWallet(): VendorWallet
    {
        return $this->wallet ?? $this->wallet()->create([
            'total_earned' => 0,
            'total_paid' => 0,
            'pending_amount' => 0,
            'hold_amount' => 0,
            'status' => VendorWallet::STATUS_ACTIVE,
        ]);
    }

    /**
     * Get the bank accounts for the vendor.
     */
    public function bankAccounts()
    {
        return $this->hasMany(VendorBankAccount::class);
    }

    /**
     * Get the primary bank account for the vendor.
     */
    public function primaryBankAccount()
    {
        return $this->hasOne(VendorBankAccount::class)->where('is_primary', true);
    }

    /**
     * Get the earnings for the vendor.
     */
    public function earnings()
    {
        return $this->hasMany(VendorEarning::class);
    }

    /**
     * Get the reviews for the vendor.
     */
    public function reviews()
    {
        return $this->hasMany(VendorReview::class);
    }

    /**
     * Get the banners for the vendor.
     */
    public function banners()
    {
        return $this->hasMany(VendorBanner::class);
    }

    /**
     * Get total followers count.
     */
    public function getTotalFollowersAttribute()
    {
        return $this->followers()->count();
    }

    /**
     * Get average rating.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->approved()->avg('rating') ?? 0;
    }

    /**
     * Get total reviews count.
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->approved()->count();
    }

    /**
     * Get referral earnings (as referrer - earnings this vendor has made)
     */
    public function referralEarnings()
    {
        return $this->hasMany(ReferralEarning::class, 'referrer_vendor_id');
    }

    /**
     * Get total referral earnings (approved + paid)
     */
    public function getTotalReferralEarningsAttribute()
    {
        return $this->referralEarnings()
            ->whereIn('status', ['approved', 'paid'])
            ->sum('commission_amount');
    }

    /**
     * Get pending referral earnings
     */
    public function getPendingReferralEarningsAttribute()
    {
        return $this->referralEarnings()
            ->where('status', 'pending')
            ->sum('commission_amount');
    }

    /**
     * Get approved (unpaid) referral earnings
     */
    public function getApprovedReferralEarningsAttribute()
    {
        return $this->referralEarnings()
            ->where('status', 'approved')
            ->sum('commission_amount');
    }

    /**
     * Get paid referral earnings
     */
    public function getPaidReferralEarningsAttribute()
    {
        return $this->referralEarnings()
            ->where('status', 'paid')
            ->sum('commission_amount');
    }
}
