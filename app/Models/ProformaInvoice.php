<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaInvoice extends Model
{
    use HasFactory;

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
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_number',
        'user_id',
        'vendor_id',
        'vendor_customer_id',
        'session_id',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'payment_note',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'invoice_data',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_data' => 'array',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the pending amount.
     *
     * @return float
     */
    public function getPendingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if the invoice is fully paid.
     *
     * @return bool
     */
    public function isFullyPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if the invoice has partial payment.
     *
     * @return bool
     */
    public function hasPartialPayment()
    {
        return $this->payment_status === 'partial';
    }

    /**
     * Get the user that owns the proforma invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the proforma invoice.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the vendor customer that owns the proforma invoice.
     */
    public function vendorCustomer()
    {
        return $this->belongsTo(VendorCustomer::class);
    }

    /**
     * Scope a query to only include invoices for a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include invoices for a specific vendor customer.
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
     * Scope a query to only include invoices for a specific session.
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
     * Get the status label for display.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return $this->status;
    }
    
    /**
     * Check if the invoice is in draft status.
     *
     * @return bool
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }
    
    /**
     * Check if the invoice is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }
    
    /**
     * Update the invoice data with new values.
     *
     * @param array $data
     * @return void
     */
    public function updateInvoiceData(array $data)
    {
        $invoiceData = $this->invoice_data;
        
        // Update cart items if provided
        if (isset($data['cart_items'])) {
            $invoiceData['cart_items'] = $data['cart_items'];
        }
        
        // Update invoice details
        $invoiceData['subtotal'] = $data['subtotal'] ?? $invoiceData['subtotal'] ?? 0;
        $invoiceData['discount_percentage'] = $data['discount_percentage'] ?? $invoiceData['discount_percentage'] ?? 0;
        $invoiceData['discount_amount'] = $data['discount_amount'] ?? $invoiceData['discount_amount'] ?? 0;
        $invoiceData['shipping'] = $data['shipping'] ?? $invoiceData['shipping'] ?? 0;
        $invoiceData['tax_percentage'] = $data['tax_percentage'] ?? $invoiceData['tax_percentage'] ?? 0;
        $invoiceData['tax_amount'] = $data['tax_amount'] ?? $invoiceData['tax_amount'] ?? 0;
        $invoiceData['total'] = $data['total'] ?? $invoiceData['total'] ?? 0;
        $invoiceData['notes'] = $data['notes'] ?? $invoiceData['notes'] ?? 'This is a proforma invoice and not a tax invoice. Payment is due upon receipt.';
        
        // Update the model
        $this->invoice_data = $invoiceData;
        $this->total_amount = $data['total'] ?? $this->total_amount;
        $this->save();
    }
}