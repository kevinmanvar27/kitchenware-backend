<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vendor_payouts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'amount',
        'status',
        'razorpay_status',
        'payment_method',
        'payout_mode',
        'transaction_id',
        'razorpay_payout_id',
        'razorpay_fund_account_id',
        'utr',
        'bank_details',
        'notes',
        'failure_reason',
        'retry_count',
        'is_automated',
        'requested_at',
        'processed_at',
        'processed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'bank_details' => 'array',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'is_automated' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FAILED = 'failed';
    const STATUS_REVERSED = 'reversed';

    /**
     * Payout mode constants
     */
    const MODE_NEFT = 'NEFT';
    const MODE_RTGS = 'RTGS';
    const MODE_IMPS = 'IMPS';
    const MODE_UPI = 'UPI';

    /**
     * Get the vendor that owns the payout.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user who processed the payout.
     */
    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the payout logs.
     */
    public function logs()
    {
        return $this->hasMany(PayoutLog::class, 'payout_id');
    }

    /**
     * Add a log entry
     */
    public function addLog(string $eventType, ?string $razorpayStatus = null, ?array $apiResponse = null, ?string $message = null): PayoutLog
    {
        return $this->logs()->create([
            'event_type' => $eventType,
            'razorpay_status' => $razorpayStatus,
            'api_response' => $apiResponse,
            'message' => $message,
        ]);
    }

    /**
     * Scope for pending payouts.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing payouts.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for completed payouts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed payouts.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for automated payouts.
     */
    public function scopeAutomated($query)
    {
        return $query->where('is_automated', true);
    }

    /**
     * Check if payout can be retried
     */
    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED && $this->retry_count < 3;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_PROCESSING => 'bg-info',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_FAILED => 'bg-danger',
            self::STATUS_REVERSED => 'bg-secondary',
            default => 'bg-secondary',
        };
    }
}