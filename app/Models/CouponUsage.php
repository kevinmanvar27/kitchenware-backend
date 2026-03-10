<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $table = 'coupon_usage';

    protected $fillable = [
        'coupon_id',
        'user_id',
        'proforma_invoice_id',
        'discount_applied',
    ];

    protected $casts = [
        'discount_applied' => 'decimal:2',
    ];

    /**
     * Get the coupon that was used
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user who used the coupon
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice where coupon was applied
     */
    public function proformaInvoice()
    {
        return $this->belongsTo(ProformaInvoice::class);
    }
}
