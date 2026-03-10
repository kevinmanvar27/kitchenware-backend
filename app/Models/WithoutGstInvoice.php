<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithoutGstInvoice extends Model
{
    use HasFactory;

    protected $table = 'without_gst_invoices';

    // Define the status options as constants
    const STATUS_DRAFT = 'Draft';
    const STATUS_APPROVED = 'Approved';
    const STATUS_DISPATCH = 'Dispatch';
    const STATUS_OUT_FOR_DELIVERY = 'Out for Delivery';
    const STATUS_DELIVERED = 'Delivered';
    const STATUS_RETURN = 'Return';
    
    // All status options
    const STATUS_OPTIONS = [
        self::STATUS_DRAFT,
        self::STATUS_APPROVED,
        self::STATUS_DISPATCH,
        self::STATUS_OUT_FOR_DELIVERY,
        self::STATUS_DELIVERED,
        self::STATUS_RETURN
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_number',
        'user_id',
        'vendor_id',
        'session_id',
        'total_amount',
        'paid_amount',
        'payment_status',
        'invoice_data',
        'status',
        'original_invoice_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'invoice_data' => 'array',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the invoice.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the pending amount.
     */
    public function getPendingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Scope a query to only include invoices for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include invoices for a specific session.
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
    
    /**
     * Check if the invoice is in draft status.
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }
    
    /**
     * Check if the invoice is approved.
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Boot method to auto-generate invoice number on creation.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate a unique invoice number for without GST invoices.
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'WGST-';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $lastInvoice = self::where('invoice_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract the sequence number and increment
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
