<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorEarning extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'vendor_earnings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vendor_id',
        'invoice_id',
        'order_amount',
        'commission_rate',
        'commission_amount',
        'vendor_earning',
        'status',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'order_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'vendor_earning' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the vendor that owns the earning.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the invoice associated with the earning.
     */
    public function invoice()
    {
        return $this->belongsTo(ProformaInvoice::class, 'invoice_id');
    }

    /**
     * Scope for pending earnings
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for confirmed earnings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope for paid earnings
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }
}
