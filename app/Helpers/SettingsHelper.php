<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserGroup;

if (!function_exists('tagline')) {
    /**
     * Get the tagline setting
     *
     * @return string
     */
    function tagline()
    {
        return setting('tagline', 'Quality hardware solutions for everyone');
    }
}

if (!function_exists('calculateDiscountedPrice')) {
    /**
     * Calculate the discounted price for a product based on user's individual or group discount
     *
     * @param float $originalPrice
     * @param User|null $user
     * @return float
     */
    function calculateDiscountedPrice($originalPrice, $user = null)
    {
        // If no user provided or not logged in, return original price
        if (!$user && !Auth::check()) {
            return $originalPrice;
        }
        
        // Use provided user or get from auth
        $user = $user ?: Auth::user();
        
        // Check for individual discount first
        if (!is_null($user->discount_percentage) && $user->discount_percentage > 0) {
            return $originalPrice * (1 - $user->discount_percentage / 100);
        }
        
        // If no individual discount, check for group discount
        $userGroups = $user->userGroups;
        if ($userGroups->count() > 0) {
            $highestGroupDiscount = 0;
            foreach ($userGroups as $group) {
                if (!is_null($group->discount_percentage) && $group->discount_percentage > $highestGroupDiscount) {
                    $highestGroupDiscount = $group->discount_percentage;
                }
            }
            
            if ($highestGroupDiscount > 0) {
                return $originalPrice * (1 - $highestGroupDiscount / 100);
            }
        }
        
        // No applicable discount, return original price
        return $originalPrice;
    }
}

if (!function_exists('setting')) {
    /**
     * Get a setting value by key with an optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        static $settings = null;
        
        if ($settings === null) {
            $settings = \App\Models\Setting::first();
        }
        
        if ($settings && array_key_exists($key, $settings->getAttributes())) {
            $value = $settings->$key;
            // For boolean values, return the value even if it's false (0)
            // Only return default if value is null
            if ($value === null) {
                return $default;
            }
            // Return default only for empty strings (not for false/0 values)
            return ($value !== '') ? $value : $default;
        }
        
        return $default;
    }
}

if (!function_exists('sidebar_text_color')) {
    /**
     * Get the sidebar text color setting
     *
     * @return string
     */
    function sidebar_text_color()
    {
        return setting('sidebar_text_color', '#333333');
    }
}

if (!function_exists('heading_text_color')) {
    /**
     * Get the heading text color setting
     *
     * @return string
     */
    function heading_text_color()
    {
        return setting('heading_text_color', '#333333');
    }
}

if (!function_exists('label_text_color')) {
    /**
     * Get the label text color setting
     *
     * @return string
     */
    function label_text_color()
    {
        return setting('label_text_color', '#333333');
    }
}

if (!function_exists('general_text_color')) {
    /**
     * Get the general text color setting
     *
     * @return string
     */
    function general_text_color()
    {
        return setting('general_text_color', '#333333');
    }
}

if (!function_exists('link_color')) {
    /**
     * Get the link color setting
     *
     * @return string
     */
    function link_color()
    {
        return setting('link_color', '#333333');
    }
}

if (!function_exists('link_hover_color')) {
    /**
     * Get the link hover color setting
     *
     * @return string
     */
    function link_hover_color()
    {
        return setting('link_hover_color', '#FF6B00');
    }
}

if (!function_exists('app_store_link')) {
    /**
     * Get the app store link setting
     *
     * @return string
     */
    function app_store_link()
    {
        return setting('app_store_link');
    }
}

if (!function_exists('play_store_link')) {
    /**
     * Get the play store link setting
     *
     * @return string
     */
    function play_store_link()
    {
        return setting('play_store_link');
    }
}

if (!function_exists('firebase_project_id')) {
    /**
     * Get the Firebase project ID setting
     *
     * @return string
     */
    function firebase_project_id()
    {
        return setting('firebase_project_id');
    }
}

if (!function_exists('firebase_client_email')) {
    /**
     * Get the Firebase client email setting
     *
     * @return string
     */
    function firebase_client_email()
    {
        return setting('firebase_client_email');
    }
}

if (!function_exists('firebase_private_key')) {
    /**
     * Get the Firebase private key setting
     *
     * @return string
     */
    function firebase_private_key()
    {
        return setting('firebase_private_key');
    }
}

if (!function_exists('is_firebase_configured')) {
    /**
     * Check if Firebase is properly configured
     *
     * @return bool
     */
    function is_firebase_configured()
    {
        return setting('firebase_project_id') && 
               setting('firebase_client_email') && 
               setting('firebase_private_key');
    }
}

if (!function_exists('user_role')) {
    /**
     * Get the current user's role
     *
     * @return string|null
     */
    function user_role()
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            return \Illuminate\Support\Facades\Auth::user()->user_role;
        }
        
        return null;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if the current user is an admin
     *
     * @return bool
     */
    function is_admin()
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            // Handle IDE false positive by explicitly checking type
            if ($user instanceof \App\Models\User) {
                return $user->isAdmin();
            }
        }
        
        return false;
    }
}

// Font size helper functions
if (!function_exists('desktop_h1_size')) {
    /**
     * Get the desktop H1 font size setting
     *
     * @return int
     */
    function desktop_h1_size()
    {
        return setting('desktop_h1_size', 36);
    }
}

if (!function_exists('desktop_h2_size')) {
    /**
     * Get the desktop H2 font size setting
     *
     * @return int
     */
    function desktop_h2_size()
    {
        return setting('desktop_h2_size', 30);
    }
}

if (!function_exists('desktop_h3_size')) {
    /**
     * Get the desktop H3 font size setting
     *
     * @return int
     */
    function desktop_h3_size()
    {
        return setting('desktop_h3_size', 24);
    }
}

if (!function_exists('desktop_h4_size')) {
    /**
     * Get the desktop H4 font size setting
     *
     * @return int
     */
    function desktop_h4_size()
    {
        return setting('desktop_h4_size', 20);
    }
}

if (!function_exists('desktop_h5_size')) {
    /**
     * Get the desktop H5 font size setting
     *
     * @return int
     */
    function desktop_h5_size()
    {
        return setting('desktop_h5_size', 18);
    }
}

if (!function_exists('desktop_h6_size')) {
    /**
     * Get the desktop H6 font size setting
     *
     * @return int
     */
    function desktop_h6_size()
    {
        return setting('desktop_h6_size', 16);
    }
}

if (!function_exists('desktop_body_size')) {
    /**
     * Get the desktop body font size setting
     *
     * @return int
     */
    function desktop_body_size()
    {
        return setting('desktop_body_size', 16);
    }
}

if (!function_exists('tablet_h1_size')) {
    /**
     * Get the tablet H1 font size setting
     *
     * @return int
     */
    function tablet_h1_size()
    {
        return setting('tablet_h1_size', 32);
    }
}

if (!function_exists('tablet_h2_size')) {
    /**
     * Get the tablet H2 font size setting
     *
     * @return int
     */
    function tablet_h2_size()
    {
        return setting('tablet_h2_size', 28);
    }
}

if (!function_exists('tablet_h3_size')) {
    /**
     * Get the tablet H3 font size setting
     *
     * @return int
     */
    function tablet_h3_size()
    {
        return setting('tablet_h3_size', 22);
    }
}

if (!function_exists('tablet_h4_size')) {
    /**
     * Get the tablet H4 font size setting
     *
     * @return int
     */
    function tablet_h4_size()
    {
        return setting('tablet_h4_size', 18);
    }
}

if (!function_exists('tablet_h5_size')) {
    /**
     * Get the tablet H5 font size setting
     *
     * @return int
     */
    function tablet_h5_size()
    {
        return setting('tablet_h5_size', 16);
    }
}

if (!function_exists('tablet_h6_size')) {
    /**
     * Get the tablet H6 font size setting
     *
     * @return int
     */
    function tablet_h6_size()
    {
        return setting('tablet_h6_size', 14);
    }
}

if (!function_exists('tablet_body_size')) {
    /**
     * Get the tablet body font size setting
     *
     * @return int
     */
    function tablet_body_size()
    {
        return setting('tablet_body_size', 14);
    }
}

if (!function_exists('mobile_h1_size')) {
    /**
     * Get the mobile H1 font size setting
     *
     * @return int
     */
    function mobile_h1_size()
    {
        return setting('mobile_h1_size', 28);
    }
}

if (!function_exists('mobile_h2_size')) {
    /**
     * Get the mobile H2 font size setting
     *
     * @return int
     */
    function mobile_h2_size()
    {
        return setting('mobile_h2_size', 24);
    }
}

if (!function_exists('mobile_h3_size')) {
    /**
     * Get the mobile H3 font size setting
     *
     * @return int
     */
    function mobile_h3_size()
    {
        return setting('mobile_h3_size', 20);
    }
}

if (!function_exists('mobile_h4_size')) {
    /**
     * Get the mobile H4 font size setting
     *
     * @return int
     */
    function mobile_h4_size()
    {
        return setting('mobile_h4_size', 16);
    }
}

if (!function_exists('mobile_h5_size')) {
    /**
     * Get the mobile H5 font size setting
     *
     * @return int
     */
    function mobile_h5_size()
    {
        return setting('mobile_h5_size', 14);
    }
}

if (!function_exists('mobile_h6_size')) {
    /**
     * Get the mobile H6 font size setting
     *
     * @return int
     */
    function mobile_h6_size()
    {
        return setting('mobile_h6_size', 12);
    }
}

if (!function_exists('mobile_body_size')) {
    /**
     * Get the mobile body font size setting
     *
     * @return int
     */
    function mobile_body_size()
    {
        return setting('mobile_body_size', 12);
    }
}

if (!function_exists('show_online_payment')) {
    /**
     * Check if online payment option should be shown.
     *
     * @return bool
     */
    function show_online_payment()
    {
        $value = setting('show_online_payment', true);
        // Ensure boolean return - handle string "0", "1", true, false, 0, 1
        if (is_bool($value)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }
}

if (!function_exists('show_cod_payment')) {
    /**
     * Check if cash on delivery option should be shown.
     *
     * @return bool
     */
    function show_cod_payment()
    {
        $value = setting('show_cod_payment', true);
        // Ensure boolean return - handle string "0", "1", true, false, 0, 1
        if (is_bool($value)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }
}

if (!function_exists('show_invoice_payment')) {
    /**
     * Check if invoice payment option should be shown.
     *
     * @return bool
     */
    function show_invoice_payment()
    {
        $value = setting('show_invoice_payment', true);
        // Ensure boolean return - handle string "0", "1", true, false, 0, 1
        if (is_bool($value)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }
}