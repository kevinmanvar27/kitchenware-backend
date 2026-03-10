<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBankAccount extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'vendor_bank_accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendor_id',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'bank_name',
        'branch_name',
        'account_type',
        'razorpay_contact_id',
        'razorpay_fund_account_id',
        'fund_account_status',
        'fund_account_error',
        'is_primary',
        'is_verified',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Fund account status constants
     */
    const FUND_STATUS_PENDING = 'pending';
    const FUND_STATUS_CREATED = 'created';
    const FUND_STATUS_FAILED = 'failed';

    /**
     * Get the vendor that owns the bank account.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Check if fund account is created in RazorpayX
     */
    public function hasFundAccount(): bool
    {
        return $this->fund_account_status === self::FUND_STATUS_CREATED 
            && !empty($this->razorpay_fund_account_id);
    }

    /**
     * Get masked account number
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        if (empty($this->account_number)) {
            return 'Not Set';
        }
        
        $length = strlen($this->account_number);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        return str_repeat('•', $length - 4) . substr($this->account_number, -4);
    }

    /**
     * Scope for primary accounts
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for accounts with fund account created
     */
    public function scopeWithFundAccount($query)
    {
        return $query->where('fund_account_status', self::FUND_STATUS_CREATED)
            ->whereNotNull('razorpay_fund_account_id');
    }
}
