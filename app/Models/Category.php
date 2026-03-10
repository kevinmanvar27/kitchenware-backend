<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
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
        'image',
        'is_active',
        'vendor_id',
    ];

    /**
     * Get the vendor that owns the category.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

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

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the image URL for the category.
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
     * Get the subcategories for the category.
     */
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }


}