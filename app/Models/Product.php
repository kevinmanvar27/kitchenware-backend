<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Product extends Model
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
        'product_type', // simple or variable
        'description',
        'mrp',
        'selling_price',
        'in_stock',
        'stock_quantity',
        'low_quantity_threshold', // Added for low stock alerts
        'status',
        'is_featured', // Featured product flag
        'main_photo',
        'product_gallery',
        'product_categories',
        'product_attributes', // For variable products
        'meta_title',
        'meta_description',
        'meta_keywords',
        'vendor_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'product_gallery' => 'array',
        'product_categories' => 'array',
        'product_attributes' => 'array',
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'low_quantity_threshold' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['main_photo_url', 'mainPhoto', 'gallery_photos'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Check if product is variable type
     */
    public function isVariable(): bool
    {
        return $this->product_type === 'variable';
    }

    /**
     * Check if product is simple type
     */
    public function isSimple(): bool
    {
        return $this->product_type === 'simple' || empty($this->product_type);
    }

    /**
     * Scope for featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get product variations
     */
    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('is_default', 'desc');
    }

    /**
     * Get the default variation
     */
    public function defaultVariation()
    {
        return $this->hasOne(ProductVariation::class)->where('is_default', true);
    }

    /**
     * Get total stock for variable products
     */
    public function getTotalStockAttribute()
    {
        if ($this->isVariable()) {
            return $this->variations()->sum('stock_quantity');
        }
        return $this->stock_quantity;
    }

    /**
     * Get price range for variable products
     * Returns array with 'min' and 'max' keys for variable products
     * Returns array with same 'min' and 'max' for simple products
     */
    public function getPriceRangeAttribute()
    {
        if ($this->isVariable()) {
            $variations = $this->variations;
            if ($variations->isEmpty()) {
                $price = $this->selling_price ?? $this->mrp ?? 0;
                return [
                    'min' => $price,
                    'max' => $price
                ];
            }
            
            $prices = $variations->map(function($v) {
                return $v->selling_price ?? $v->mrp;
            })->filter();
            
            if ($prices->isEmpty()) {
                $price = $this->selling_price ?? $this->mrp ?? 0;
                return [
                    'min' => $price,
                    'max' => $price
                ];
            }
            
            $minPrice = $prices->min();
            $maxPrice = $prices->max();
            
            return [
                'min' => $minPrice,
                'max' => $maxPrice
            ];
        }
        
        // For simple products, return same min and max
        $price = $this->selling_price ?? $this->mrp ?? 0;
        return [
            'min' => $price,
            'max' => $price
        ];
    }

    /**
     * Get MRP range for variable products
     * Returns array with 'min' and 'max' keys for variable products
     * Returns array with same 'min' and 'max' for simple products
     */
    public function getMrpRangeAttribute()
    {
        if ($this->isVariable()) {
            $variations = $this->variations;
            if ($variations->isEmpty()) {
                $mrp = $this->mrp ?? 0;
                return [
                    'min' => $mrp,
                    'max' => $mrp
                ];
            }
            
            $mrps = $variations->map(function($v) {
                return $v->mrp ?? 0;
            })->filter();
            
            if ($mrps->isEmpty()) {
                $mrp = $this->mrp ?? 0;
                return [
                    'min' => $mrp,
                    'max' => $mrp
                ];
            }
            
            $minMrp = $mrps->min();
            $maxMrp = $mrps->max();
            
            return [
                'min' => $minMrp,
                'max' => $maxMrp
            ];
        }
        
        // For simple products, return same min and max
        $mrp = $this->mrp ?? 0;
        return [
            'min' => $mrp,
            'max' => $mrp
        ];
    }

    /**
     * Check if the product has low stock
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        $threshold = $this->low_quantity_threshold ?? 10;
        
        if ($this->isVariable()) {
            // Check if any variation has low stock
            return $this->variations()->where('in_stock', true)
                ->whereColumn('stock_quantity', '<=', \DB::raw($threshold))
                ->exists();
        }
        
        return $this->in_stock && $this->stock_quantity <= $threshold;
    }

    /**
     * Get all products with low stock
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLowStockProducts()
    {
        return static::where('in_stock', true)
            ->whereColumn('stock_quantity', '<=', 'low_quantity_threshold')
            ->get();
    }

    /**
     * Get the main photo URL for the product.
     */
    public function getMainPhotoUrlAttribute()
    {
        $mainPhotoPath = $this->attributes['main_photo'] ?? null;
        
        if ($mainPhotoPath) {
            return asset('storage/' . $mainPhotoPath);
        }
        return null;
    }

    /**
     * Get the mainPhoto as an object (for relationship-like access).
     * This provides compatibility with code expecting $product->mainPhoto->url
     */
    public function getMainPhotoAttribute()
    {
        // Check if 'main_photo' column exists in attributes
        $mainPhotoPath = $this->attributes['main_photo'] ?? null;
        
        if ($mainPhotoPath) {
            return (object) [
                'path' => $mainPhotoPath,
                'url' => asset('storage/' . $mainPhotoPath)
            ];
        }
        
        return null;
    }

    /**
     * Get the gallery photos URLs for the product.
     */
    public function getGalleryPhotosAttribute()
    {
        if (empty($this->product_gallery)) {
            return [];
        }

        return collect($this->product_gallery)->map(function($path) {
            return [
                'path' => $path,
                'url' => asset('storage/' . $path)
            ];
        })->toArray();
    }

    /**
     * Get the vendor that owns the product.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }



    /**
     * Get the primary/first category for the product.
     * Returns the first category from product_categories JSON field.
     */
    public function getCategoryAttribute()
    {
        if (empty($this->product_categories) || !is_array($this->product_categories)) {
            return null;
        }

        $firstCategory = collect($this->product_categories)->first();
        $firstCategoryId = is_array($firstCategory) ? ($firstCategory['category_id'] ?? null) : null;
        
        if ($firstCategoryId) {
            return Category::find($firstCategoryId);
        }
        
        return null;
    }

    /**
     * Get the categories for the product.
     */
    public function getCategoriesAttribute()
    {
        if (empty($this->product_categories)) {
            return new Collection();
        }

        $categoryIds = collect($this->product_categories)->pluck('category_id')->toArray();
        return Category::whereIn('id', $categoryIds)->get();
    }

    /**
     * Get the subcategories for the product.
     */
    public function getSubCategoriesAttribute()
    {
        if (empty($this->product_categories)) {
            return new Collection();
        }

        $subcategoryIds = collect($this->product_categories)
            ->pluck('subcategory_ids')
            ->flatten()
            ->toArray();

        return SubCategory::whereIn('id', $subcategoryIds)->get();
    }

    /**
     * Get the product views for analytics.
     */
    public function views()
    {
        return $this->hasMany(ProductView::class);
    }

    /**
     * Get the wishlist items for this product.
     */
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get total wishlist count for this product.
     */
    public function getWishlistCountAttribute()
    {
        return $this->wishlistItems()->count();
    }

    /**
     * Check if a specific user has this product in wishlist.
     *
     * @param int $userId
     * @return bool
     */
    public function isInUserWishlist($userId): bool
    {
        return $this->wishlistItems()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a specific vendor customer has this product in wishlist.
     *
     * @param int $vendorCustomerId
     * @return bool
     */
    public function isInVendorCustomerWishlist($vendorCustomerId): bool
    {
        return $this->wishlistItems()->where('vendor_customer_id', $vendorCustomerId)->exists();
    }

    /**
     * Get total view count for this product.
     */
    public function getViewCountAttribute()
    {
        return $this->views()->count();
    }

    /**
     * Get unique visitor count for this product.
     */
    public function getUniqueViewCountAttribute()
    {
        return $this->views()->distinct('session_id')->count('session_id');
    }
}