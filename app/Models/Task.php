<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'attachment',
        'assigned_by',
        'assigned_to',
        'vendor_id',
        'status',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_QUESTION = 'question';
    const STATUS_DONE = 'done';
    const STATUS_VERIFIED = 'verified';

    /**
     * Get all available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_QUESTION => 'Question',
            self::STATUS_DONE => 'Done',
            self::STATUS_VERIFIED => 'Verified',
        ];
    }

    /**
     * Get the user who assigned the task (admin)
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user to whom the task is assigned (vendor staff)
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the vendor associated with the task
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get all comments for the task
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the attachment URL
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            return asset('storage/tasks/' . $this->attachment);
        }
        return null;
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute()
    {
        $statuses = self::getStatuses();
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get the status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_QUESTION => 'danger',
            self::STATUS_DONE => 'success',
            self::STATUS_VERIFIED => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Check if task is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if task is in progress
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if task has a question
     */
    public function hasQuestion()
    {
        return $this->status === self::STATUS_QUESTION;
    }

    /**
     * Check if task is done
     */
    public function isDone()
    {
        return $this->status === self::STATUS_DONE;
    }

    /**
     * Check if task is verified
     */
    public function isVerified()
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Scope to filter tasks by vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope to filter tasks by assigned user
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to filter tasks by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending tasks
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get in progress tasks
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope to get tasks with questions
     */
    public function scopeWithQuestions($query)
    {
        return $query->where('status', self::STATUS_QUESTION);
    }

    /**
     * Scope to get done tasks
     */
    public function scopeDone($query)
    {
        return $query->where('status', self::STATUS_DONE);
    }

    /**
     * Scope to get verified tasks
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }
}
