<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VendorBanner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'title',
        'image_path',
        'redirect_url',
        'is_active',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the vendor that owns the banner.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            // URL-encode the filename to handle spaces and special characters
            $encodedFilename = rawurlencode(basename($this->image_path));
            $directory = dirname($this->image_path);
            
            // Construct the full path
            $fullPath = $directory !== '.' ? $directory . '/' . $encodedFilename : $encodedFilename;
            
            if (Storage::disk('public')->exists($this->image_path)) {
                return asset('storage/' . $fullPath);
            }
        }
        
        return null;
    }

    /**
     * Scope for active banners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered banners.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * Delete the banner image from storage.
     */
    public function deleteImage()
    {
        if ($this->image_path) {
            $path = str_replace('storage/', '', $this->image_path);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete image when banner is deleted
        static::deleting(function ($banner) {
            $banner->deleteImage();
        });
    }
}
