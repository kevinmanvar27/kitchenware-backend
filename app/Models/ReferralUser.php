<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'referral_id',
        'user_id',
        'name',
        'email',
        'phone_number',
        'notes',
        'payment_status',
        'payment_amount',
        'paid_at',
        'payment_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the referral that this user belongs to.
     */
    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Get the user account if linked.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is completed.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Get the payment status badge class.
     */
    public function getPaymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    /**
     * Scope to get only pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope to get only paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}
