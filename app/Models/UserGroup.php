<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserGroupMember;

class UserGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_percentage' => 'decimal:2',
    ];

    /**
     * Get the users that belong to this group.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group_members');
    }

    /**
     * Get the members of this group.
     */
    public function members()
    {
        return $this->hasMany(UserGroupMember::class);
    }
}