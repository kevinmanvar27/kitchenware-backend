<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorStaff extends Model
{
    use HasFactory;

    protected $table = 'vendor_staff';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'user_id',
        'role',
        'permissions',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vendor that owns the staff member.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user associated with the staff member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if staff has a specific permission.
     */
    public function hasPermission($permission): bool
    {
        if (!$this->permissions) {
            return false;
        }
        
        return in_array($permission, $this->permissions);
    }

    /**
     * Check if staff has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->permissions) {
            return false;
        }
        
        return !empty(array_intersect($permissions, $this->permissions));
    }
}
