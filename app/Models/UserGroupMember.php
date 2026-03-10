<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroupMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_group_id',
        'user_id',
    ];

    /**
     * Get the user group that this member belongs to.
     */
    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Get the user that this member represents.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}