<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'image',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subCategory) {
            if (empty($subCategory->slug)) {
                $subCategory->slug = Str::slug($subCategory->name);
            }
        });

        static::updating(function ($subCategory) {
            if (empty($subCategory->slug)) {
                $subCategory->slug = Str::slug($subCategory->name);
            }
        });
    }

    /**
     * Get the image URL for the subcategory.
     */
    public function getImageUrlAttribute()
    {
        if ($this->attributes['image'] ?? null) {
            $imagePath = $this->attributes['image'];
            
            // Check if file exists in storage
            if (\Storage::disk('public')->exists($imagePath)) {
                return asset('storage/' . $imagePath);
            }
            
            // Fallback: return the path anyway (for backward compatibility)
            return asset('storage/' . $imagePath);
        }
        return null; // No image
    }

    /**
     * Get the parent category for the subcategory.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }


}