<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'mrp',
        'selling_price',
        'stock_quantity',
        'low_quantity_threshold',
        'in_stock',
        'image',
        'attribute_values',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'in_stock' => 'boolean',
        'is_default' => 'boolean',
        'attribute_values' => 'array',
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['image_url'];

    /**
     * Get parent product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get variation image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->attributes['image'] ?? null) {
            return asset('storage/' . $this->attributes['image']);
        }
        return null;
    }

    /**
     * Get formatted attribute values
     */
    public function getFormattedAttributesAttribute()
    {
        if (empty($this->attribute_values) || !is_array($this->attribute_values)) {
            return [];
        }

        $formatted = [];
        foreach ($this->attribute_values as $attrId => $valueId) {
            $attribute = ProductAttribute::find($attrId);
            $value = ProductAttributeValue::find($valueId);
            
            if ($attribute && $value) {
                $formatted[$attribute->name] = $value->value;
            }
        }

        return $formatted;
    }

    /**
     * Get display name for variation
     */
    public function getDisplayNameAttribute()
    {
        $attributes = $this->formatted_attributes;
        if (empty($attributes) || !is_array($attributes)) {
            return $this->product->name ?? 'Product Variation';
        }

        $productName = $this->product->name ?? 'Product';
        return $productName . ' - ' . implode(', ', $attributes);
    }

    /**
     * Get the price to display (selling price or MRP)
     */
    public function getPriceAttribute()
    {
        return $this->selling_price ?? $this->mrp ?? $this->product->selling_price ?? $this->product->mrp;
    }

    /**
     * Check if the variation has low stock
     */
    public function isLowStock(): bool
    {
        // Use variation-specific threshold if set, otherwise fall back to product threshold
        $threshold = $this->low_quantity_threshold ?? $this->product->low_quantity_threshold ?? 10;
        return $this->in_stock && $this->stock_quantity <= $threshold;
    }
}
