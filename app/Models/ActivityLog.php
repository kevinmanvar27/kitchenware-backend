<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'log_type',
        'user_id',
        'vendor_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Log type constants
     */
    const TYPE_ADMIN = 'admin';
    const TYPE_VENDOR = 'vendor';

    /**
     * Action constants
     */
    const ACTION_CREATE = 'created';
    const ACTION_UPDATE = 'updated';
    const ACTION_DELETE = 'deleted';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_VIEW = 'viewed';
    const ACTION_EXPORT = 'exported';
    const ACTION_IMPORT = 'imported';
    const ACTION_APPROVE = 'approved';
    const ACTION_REJECT = 'rejected';
    const ACTION_SUSPEND = 'suspended';

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor associated with this log.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the subject model.
     */
    public function subject()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Scope for admin logs.
     */
    public function scopeAdmin($query)
    {
        return $query->where('log_type', self::TYPE_ADMIN);
    }

    /**
     * Scope for vendor logs.
     */
    public function scopeVendor($query)
    {
        return $query->where('log_type', self::TYPE_VENDOR);
    }

    /**
     * Scope for a specific vendor.
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Get a human-readable model name.
     */
    public function getModelNameAttribute()
    {
        if (!$this->model_type) {
            return null;
        }
        
        $parts = explode('\\', $this->model_type);
        return end($parts);
    }

    /**
     * Get action badge color.
     */
    public function getActionColorAttribute()
    {
        return match($this->action) {
            self::ACTION_CREATE => 'success',
            self::ACTION_UPDATE => 'info',
            self::ACTION_DELETE => 'danger',
            self::ACTION_LOGIN => 'primary',
            self::ACTION_LOGOUT => 'secondary',
            self::ACTION_VIEW => 'light',
            self::ACTION_EXPORT => 'warning',
            self::ACTION_IMPORT => 'warning',
            self::ACTION_APPROVE => 'success',
            self::ACTION_REJECT => 'danger',
            self::ACTION_SUSPEND => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get action icon.
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            self::ACTION_CREATE => 'fa-plus-circle',
            self::ACTION_UPDATE => 'fa-edit',
            self::ACTION_DELETE => 'fa-trash',
            self::ACTION_LOGIN => 'fa-sign-in-alt',
            self::ACTION_LOGOUT => 'fa-sign-out-alt',
            self::ACTION_VIEW => 'fa-eye',
            self::ACTION_EXPORT => 'fa-download',
            self::ACTION_IMPORT => 'fa-upload',
            self::ACTION_APPROVE => 'fa-check-circle',
            self::ACTION_REJECT => 'fa-times-circle',
            self::ACTION_SUSPEND => 'fa-ban',
            default => 'fa-circle',
        };
    }

    /**
     * Log an admin activity.
     */
    public static function logAdmin($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        $user = auth()->user();
        
        return self::create([
            'log_type' => self::TYPE_ADMIN,
            'user_id' => $user?->id,
            'vendor_id' => null,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a vendor activity.
     */
    public static function logVendor($vendorId, $action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        $user = auth()->user();
        
        return self::create([
            'log_type' => self::TYPE_VENDOR,
            'user_id' => $user?->id,
            'vendor_id' => $vendorId,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
