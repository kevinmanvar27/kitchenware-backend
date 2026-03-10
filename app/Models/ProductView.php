<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'referrer',
        'device_type',
        'browser',
        'country',
        'country_code',
        'region',
        'city',
        'latitude',
        'longitude',
    ];

    /**
     * Get the product that was viewed.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who viewed the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Detect device type from user agent
     */
    public static function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/(mobile|iphone|ipod|android|blackberry|opera mini|iemobile)/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Detect browser from user agent
     */
    public static function detectBrowser(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        $browsers = [
            'Edge' => '/edge|edg/i',
            'Opera' => '/opera|opr/i',
            'Chrome' => '/chrome/i',
            'Safari' => '/safari/i',
            'Firefox' => '/firefox/i',
            'IE' => '/msie|trident/i',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }

        return 'Other';
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get unique views (by session or user)
     */
    public function scopeUniqueViews($query)
    {
        return $query->select('product_id')
            ->selectRaw('COUNT(DISTINCT COALESCE(user_id, session_id)) as unique_views')
            ->groupBy('product_id');
    }
}
