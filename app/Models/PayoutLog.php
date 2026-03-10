<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'payout_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payout_id',
        'event_type',
        'razorpay_status',
        'api_response',
        'message',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'api_response' => 'array',
    ];

    /**
     * Event type constants
     */
    const EVENT_INITIATED = 'initiated';
    const EVENT_PROCESSING = 'processing';
    const EVENT_COMPLETED = 'completed';
    const EVENT_FAILED = 'failed';
    const EVENT_REVERSED = 'reversed';

    /**
     * Get the payout that owns the log.
     */
    public function payout()
    {
        return $this->belongsTo(VendorPayout::class, 'payout_id');
    }
}
