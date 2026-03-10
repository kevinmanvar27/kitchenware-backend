<?php

namespace App\Http\Controllers\API;

use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="App Configuration",
 *     description="API Endpoints for App Configuration and Settings"
 * )
 */
class AppConfigController extends ApiController
{
    /**
     * Get app settings (theme, colors, fonts)
     * 
     * @OA\Get(
     *      path="/api/v1/app-settings",
     *      operationId="getAppSettings",
     *      tags={"App Configuration"},
     *      summary="Get app settings",
     *      description="Get app theme settings including colors and fonts",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function appSettings()
    {
        $settings = [
            // Theme colors - using actual database column names
            'theme_color' => $this->getSetting('theme_color', '#FF6B00'),
            'primary_color' => $this->getSetting('theme_color', '#FF6B00'), // alias for theme_color
            'secondary_color' => $this->getSetting('background_color', '#FFFFFF'),
            'background_color' => $this->getSetting('background_color', '#FFFFFF'),
            'font_color' => $this->getSetting('font_color', '#333333'),
            'text_color' => $this->getSetting('font_color', '#333333'), // alias for font_color
            'sidebar_text_color' => $this->getSetting('sidebar_text_color', '#333333'),
            'heading_text_color' => $this->getSetting('heading_text_color', '#333333'),
            'label_text_color' => $this->getSetting('label_text_color', '#333333'),
            'general_text_color' => $this->getSetting('general_text_color', '#333333'),
            'link_color' => $this->getSetting('link_color', '#333333'),
            'link_hover_color' => $this->getSetting('link_hover_color', '#FF6B00'),
            'header_color' => $this->getSetting('theme_color', '#FF6B00'),
            'footer_color' => $this->getSetting('theme_color', '#FF6B00'),
            'accent_color' => $this->getSetting('link_hover_color', '#FF6B00'),
            
            // Fonts
            'primary_font' => $this->getSetting('body_font_family', 'Roboto'),
            'secondary_font' => $this->getSetting('h1_font_family', 'Open Sans'),
            'font_size_base' => $this->getSetting('desktop_body_size', '16'),
            'font_style' => $this->getSetting('font_style', 'normal'),
            
            // Font families (element-wise)
            'h1_font_family' => $this->getSetting('h1_font_family', 'Roboto'),
            'h2_font_family' => $this->getSetting('h2_font_family', 'Roboto'),
            'h3_font_family' => $this->getSetting('h3_font_family', 'Roboto'),
            'h4_font_family' => $this->getSetting('h4_font_family', 'Roboto'),
            'h5_font_family' => $this->getSetting('h5_font_family', 'Roboto'),
            'h6_font_family' => $this->getSetting('h6_font_family', 'Roboto'),
            'body_font_family' => $this->getSetting('body_font_family', 'Roboto'),
            
            // App appearance
            'dark_mode_enabled' => $this->getSetting('dark_mode_enabled', 'false') === 'true',
            'logo_url' => $this->getLogoUrl('header_logo'),
            'footer_logo_url' => $this->getLogoUrl('footer_logo'),
            'favicon_url' => $this->getLogoUrl('favicon'),
            'app_icon_url' => $this->getSetting('app_icon_url', null),
            'splash_screen_url' => $this->getSetting('splash_screen_url', null),
            
            // Branding
            'brand_name' => $this->getSetting('site_title', config('app.name')),
            'site_title' => $this->getSetting('site_title', config('app.name')),
            'site_description' => $this->getSetting('site_description', ''),
            'tagline' => $this->getSetting('tagline', ''),
        ];

        return $this->sendResponse($settings, 'App settings retrieved successfully.');
    }

    /**
     * Get app configuration (public - no auth required)
     * 
     * @OA\Get(
     *      path="/api/v1/app-config",
     *      operationId="getAppConfigPublic",
     *      tags={"App Configuration"},
     *      summary="Get app configuration (public)",
     *      description="Get app configuration including general settings. This is a public endpoint.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function appConfigPublic()
    {
        $config = [
            // General app config
            'app_name' => config('app.name'),
            'app_version' => $this->getSetting('app_version', '1.0.0'),
            'min_app_version' => $this->getSetting('min_app_version', '1.0.0'),
            'force_update' => $this->getSetting('force_update', 'false') === 'true',
            'maintenance_mode' => $this->getSetting('maintenance_mode', 'false') === 'true',
            'maintenance_message' => $this->getSetting('maintenance_message', 'App is under maintenance. Please try again later.'),
            
            // Feature flags
            'features' => [
                'cart_enabled' => $this->getSetting('cart_enabled', 'true') === 'true',
                'invoices_enabled' => $this->getSetting('invoices_enabled', 'true') === 'true',
                'notifications_enabled' => $this->getSetting('notifications_enabled', 'true') === 'true',
                'search_enabled' => $this->getSetting('search_enabled', 'true') === 'true',
                'profile_edit_enabled' => $this->getSetting('profile_edit_enabled', 'true') === 'true',
                'pdf_download_enabled' => $this->getSetting('pdf_download_enabled', 'true') === 'true',
            ],
            
            // Pagination defaults
            'pagination' => [
                'default_per_page' => (int) $this->getSetting('default_per_page', '15'),
                'max_per_page' => (int) $this->getSetting('max_per_page', '50'),
            ],
            
            // Currency settings
            'currency' => [
                'code' => $this->getSetting('currency_code', 'INR'),
                'symbol' => $this->getSetting('currency_symbol', '₹'),
                'position' => $this->getSetting('currency_position', 'before'),
                'decimal_places' => (int) $this->getSetting('decimal_places', '2'),
            ],
            
            // Tax settings
            'tax' => [
                'enabled' => $this->getSetting('tax_enabled', 'true') === 'true',
                'rate' => (float) $this->getSetting('tax_rate', '18'),
                'label' => $this->getSetting('tax_label', 'GST'),
                'inclusive' => $this->getSetting('tax_inclusive', 'false') === 'true',
            ],
            
            // Discount settings
            'discount' => [
                'enabled' => $this->getSetting('discount_enabled', 'true') === 'true',
                'default_discount' => (float) $this->getSetting('default_discount', '0'),
            ],
            
            // Order settings
            'order' => [
                'min_order_amount' => (float) $this->getSetting('min_order_amount', '0'),
                'max_order_amount' => (float) $this->getSetting('max_order_amount', '0'),
                'min_quantity' => (int) $this->getSetting('min_quantity', '1'),
                'max_quantity' => (int) $this->getSetting('max_quantity', '999'),
            ],
            
            // Frontend access permissions
            'frontend_access' => [
                'permission' => $this->getSetting('frontend_access_permission', 'open_for_all'),
                'pending_approval_message' => $this->getSetting('pending_approval_message', 'Your account is pending approval. Please wait for admin approval before accessing the site.'),
            ],
            
            // Payment method visibility settings
            'payment_methods' => [
                'show_online_payment' => $this->getBooleanSetting('show_online_payment', true),
                'show_cod_payment' => $this->getBooleanSetting('show_cod_payment', true),
                'show_invoice_payment' => $this->getBooleanSetting('show_invoice_payment', true),
            ],
        ];

        return $this->sendResponse($config, 'App configuration retrieved successfully.');
    }

    /**
     * Get app configuration (access permissions, general config)
     * 
     * @OA\Get(
     *      path="/api/v1/app-config",
     *      operationId="getAppConfig",
     *      tags={"App Configuration"},
     *      summary="Get app configuration",
     *      description="Get app configuration including access permissions and general settings",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appConfig(Request $request)
    {
        $user = $request->user();
        
        $config = [
            // General app config
            'app_name' => config('app.name'),
            'app_version' => $this->getSetting('app_version', '1.0.0'),
            'min_app_version' => $this->getSetting('min_app_version', '1.0.0'),
            'force_update' => $this->getSetting('force_update', 'false') === 'true',
            'maintenance_mode' => $this->getSetting('maintenance_mode', 'false') === 'true',
            'maintenance_message' => $this->getSetting('maintenance_message', 'App is under maintenance. Please try again later.'),
            
            // Feature flags
            'features' => [
                'cart_enabled' => $this->getSetting('cart_enabled', 'true') === 'true',
                'invoices_enabled' => $this->getSetting('invoices_enabled', 'true') === 'true',
                'notifications_enabled' => $this->getSetting('notifications_enabled', 'true') === 'true',
                'search_enabled' => $this->getSetting('search_enabled', 'true') === 'true',
                'profile_edit_enabled' => $this->getSetting('profile_edit_enabled', 'true') === 'true',
                'pdf_download_enabled' => $this->getSetting('pdf_download_enabled', 'true') === 'true',
            ],
            
            // Access permissions based on user role
            'permissions' => $this->getUserPermissions($user),
            
            // Pagination defaults
            'pagination' => [
                'default_per_page' => (int) $this->getSetting('default_per_page', '15'),
                'max_per_page' => (int) $this->getSetting('max_per_page', '50'),
            ],
            
            // Currency settings
            'currency' => [
                'code' => $this->getSetting('currency_code', 'INR'),
                'symbol' => $this->getSetting('currency_symbol', '₹'),
                'position' => $this->getSetting('currency_position', 'before'),
                'decimal_places' => (int) $this->getSetting('decimal_places', '2'),
            ],
            
            // Tax settings
            'tax' => [
                'enabled' => $this->getSetting('tax_enabled', 'true') === 'true',
                'rate' => (float) $this->getSetting('tax_rate', '18'),
                'label' => $this->getSetting('tax_label', 'GST'),
                'inclusive' => $this->getSetting('tax_inclusive', 'false') === 'true',
            ],
            
            // Discount settings
            'discount' => [
                'enabled' => $this->getSetting('discount_enabled', 'true') === 'true',
                'default_discount' => (float) $this->getSetting('default_discount', '0'),
            ],
            
            // Order settings
            'order' => [
                'min_order_amount' => (float) $this->getSetting('min_order_amount', '0'),
                'max_order_amount' => (float) $this->getSetting('max_order_amount', '0'),
                'min_quantity' => (int) $this->getSetting('min_quantity', '1'),
                'max_quantity' => (int) $this->getSetting('max_quantity', '999'),
            ],
            
            // Frontend access permissions
            'frontend_access' => [
                'permission' => $this->getSetting('frontend_access_permission', 'open_for_all'),
                'pending_approval_message' => $this->getSetting('pending_approval_message', 'Your account is pending approval. Please wait for admin approval before accessing the site.'),
            ],
            
            // Payment method visibility settings
            'payment_methods' => [
                'show_online_payment' => $this->getBooleanSetting('show_online_payment', true),
                'show_cod_payment' => $this->getBooleanSetting('show_cod_payment', true),
                'show_invoice_payment' => $this->getBooleanSetting('show_invoice_payment', true),
            ],
        ];

        return $this->sendResponse($config, 'App configuration retrieved successfully.');
    }

    /**
     * Get company information
     * 
     * @OA\Get(
     *      path="/api/v1/company-info",
     *      operationId="getCompanyInfo",
     *      tags={"App Configuration"},
     *      summary="Get company information",
     *      description="Get company details including address, contact info, and social links. This is a public endpoint.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function companyInfo()
    {
        $companyInfo = [
            // Basic info
            'name' => $this->getSetting('company_name', config('app.name')),
            'legal_name' => $this->getSetting('company_legal_name', ''),
            'tagline' => $this->getSetting('company_tagline', ''),
            'description' => $this->getSetting('company_description', ''),
            
            // Contact info
            'email' => $this->getSetting('company_email', ''),
            'phone' => $this->getSetting('company_phone', ''),
            'whatsapp' => $this->getSetting('company_whatsapp', ''),
            'fax' => $this->getSetting('company_fax', ''),
            
            // Address
            'address' => [
                'street' => $this->getSetting('company_street', ''),
                'city' => $this->getSetting('company_city', ''),
                'state' => $this->getSetting('company_state', ''),
                'postal_code' => $this->getSetting('company_postal_code', ''),
                'country' => $this->getSetting('company_country', ''),
                'full_address' => $this->getSetting('company_full_address', ''),
            ],
            
            // Business details
            'gst_number' => $this->getSetting('company_gst_number', ''),
            'pan_number' => $this->getSetting('company_pan_number', ''),
            'cin_number' => $this->getSetting('company_cin_number', ''),
            'registration_number' => $this->getSetting('company_registration_number', ''),
            
            // Bank details
            'bank_details' => [
                'bank_name' => $this->getSetting('bank_name', ''),
                'account_name' => $this->getSetting('bank_account_name', ''),
                'account_number' => $this->getSetting('bank_account_number', ''),
                'ifsc_code' => $this->getSetting('bank_ifsc_code', ''),
                'branch' => $this->getSetting('bank_branch', ''),
            ],
            
            // Social links
            'social_links' => [
                'website' => $this->getSetting('social_website', ''),
                'facebook' => $this->getSetting('social_facebook', ''),
                'twitter' => $this->getSetting('social_twitter', ''),
                'instagram' => $this->getSetting('social_instagram', ''),
                'linkedin' => $this->getSetting('social_linkedin', ''),
                'youtube' => $this->getSetting('social_youtube', ''),
            ],
            
            // Support
            'support' => [
                'email' => $this->getSetting('support_email', ''),
                'phone' => $this->getSetting('support_phone', ''),
                'hours' => $this->getSetting('support_hours', ''),
            ],
            
            // Legal
            'legal' => [
                'terms_url' => $this->getSetting('terms_url', ''),
                'privacy_url' => $this->getSetting('privacy_url', ''),
                'refund_policy_url' => $this->getSetting('refund_policy_url', ''),
            ],
        ];

        return $this->sendResponse($companyInfo, 'Company information retrieved successfully.');
    }

    /**
     * Check app version and update requirements
     * 
     * @OA\Get(
     *      path="/api/v1/app-version",
     *      operationId="checkAppVersion",
     *      tags={"App Configuration"},
     *      summary="Check app version",
     *      description="Check if app update is required or available. This is a public endpoint that does not require authentication.",
     *      @OA\Parameter(
     *          name="platform",
     *          description="Mobile platform (ios or android)",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"ios", "android"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="current_version",
     *          description="Current app version installed on device (e.g., 1.0.0)",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="1.0.0"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="App version check completed."),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="platform", type="string", example="android"),
     *                  @OA\Property(property="current_version", type="string", example="1.0.0"),
     *                  @OA\Property(property="latest_version", type="string", example="1.2.0"),
     *                  @OA\Property(property="minimum_version", type="string", example="1.0.0"),
     *                  @OA\Property(property="update_required", type="boolean", example=false, description="True if current version is below minimum required version"),
     *                  @OA\Property(property="update_available", type="boolean", example=true, description="True if a newer version is available"),
     *                  @OA\Property(property="update_url", type="string", example="https://play.google.com/store/apps/details?id=com.example.app", description="URL to app store for update"),
     *                  @OA\Property(property="release_notes", type="string", nullable=true, example="Bug fixes and performance improvements", description="Release notes for latest version")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="The platform field is required.")
     *          )
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appVersion(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|in:ios,android',
            'current_version' => 'required|string|regex:/^\d+\.\d+\.\d+$/',
        ]);

        $platform = strtolower($request->platform);
        $currentVersion = $request->current_version;

        // Get platform-specific version settings
        $latestVersion = $platform === 'ios' 
            ? $this->getSetting('app_version_ios', '1.0.0')
            : $this->getSetting('app_version_android', '1.0.0');

        $minimumVersion = $platform === 'ios'
            ? $this->getSetting('min_version_ios', '1.0.0')
            : $this->getSetting('min_version_android', '1.0.0');

        $storeUrl = $platform === 'ios'
            ? $this->getSetting('ios_store_url', '')
            : $this->getSetting('android_store_url', '');

        $releaseNotes = $platform === 'ios'
            ? $this->getSetting('release_notes_ios', null)
            : $this->getSetting('release_notes_android', null);

        // Compare versions
        $updateRequired = version_compare($currentVersion, $minimumVersion, '<');
        $updateAvailable = version_compare($currentVersion, $latestVersion, '<');

        $data = [
            'platform' => $platform,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'minimum_version' => $minimumVersion,
            'update_required' => $updateRequired,
            'update_available' => $updateAvailable,
            'update_url' => $storeUrl,
            'release_notes' => $updateAvailable ? $releaseNotes : null,
        ];

        return $this->sendResponse($data, 'App version check completed.');
    }

    /**
     * Get logo/image URL from setting
     * 
     * @param string $key
     * @return string|null
     */
    private function getLogoUrl($key)
    {
        $value = $this->getSetting($key, null);
        
        if (empty($value)) {
            return null;
        }
        
        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Otherwise, prepend the app URL
        return rtrim(config('app.url'), '/') . '/' . ltrim($value, '/');
    }

    /**
     * Get a setting value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getSetting($key, $default = null)
    {
        // Get the first (and only) settings row, cache it for the request
        static $settings = null;
        
        if ($settings === null) {
            $settings = Setting::first();
        }
        
        // If no settings row exists or the column doesn't exist, return default
        if (!$settings || !isset($settings->$key)) {
            return $default;
        }
        
        return $settings->$key ?? $default;
    }

    /**
     * Get a boolean setting value
     * 
     * @param string $key
     * @param bool $default
     * @return bool
     */
    private function getBooleanSetting($key, $default = false)
    {
        $value = $this->getSetting($key, $default);
        
        // If already boolean, return as is
        if (is_bool($value)) {
            return $value;
        }
        
        // Handle various truthy/falsy values
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * Get user permissions based on role
     * 
     * @param \App\Models\User $user
     * @return array
     */
    private function getUserPermissions($user)
    {
        $basePermissions = [
            'can_view_products' => true,
            'can_view_categories' => true,
            'can_search' => true,
            'can_view_profile' => true,
            'can_edit_profile' => true,
            'can_view_cart' => true,
            'can_add_to_cart' => true,
            'can_create_invoice' => true,
            'can_view_invoices' => true,
            'can_download_pdf' => true,
            'can_view_notifications' => true,
        ];

        // Admin/Super Admin permissions
        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            $basePermissions = array_merge($basePermissions, [
                'can_manage_products' => true,
                'can_manage_categories' => true,
                'can_manage_users' => true,
                'can_manage_invoices' => true,
                'can_manage_settings' => true,
                'can_view_reports' => true,
            ]);
        }

        return $basePermissions;
    }

    /**
     * Get Razorpay configuration (public - no auth required)
     * 
     * @OA\Get(
     *      path="/api/v1/razorpay-config",
     *      operationId="getRazorpayConfig",
     *      tags={"App Configuration"},
     *      summary="Get Razorpay configuration",
     *      description="Get Razorpay Key ID and Secret for payment integration. This is a public endpoint for Flutter app integration.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Razorpay configuration retrieved successfully."),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="key_id", type="string", example="rzp_test_xxxxxxxxxx", description="Razorpay Key ID for payment integration"),
     *                  @OA\Property(property="key_secret", type="string", example="test_secret_xxxxxxxxxx", description="Razorpay Key Secret for payment verification"),
     *                  @OA\Property(property="is_configured", type="boolean", example=true, description="Whether Razorpay is configured"),
     *                  @OA\Property(property="currency", type="string", example="INR", description="Default currency")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=503,
     *          description="Service Unavailable - Razorpay not configured",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Razorpay service is not configured")
     *          )
     *      )
     * )
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function razorpayConfig()
    {
        $keyId = $this->getSetting('razorpay_key_id', null);
        $keySecret = $this->getSetting('razorpay_key_secret', null);
        
        // Check if Razorpay is configured
        if (empty($keyId) || empty($keySecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Razorpay service is not configured',
                'data' => [
                    'is_configured' => false,
                    'key_id' => null,
                    'key_secret' => null,
                    'currency' => 'INR',
                ]
            ], 503);
        }

        $config = [
            'key_id' => $keyId,
            'key_secret' => $keySecret,
            'is_configured' => true,
            'currency' => $this->getSetting('currency_code', 'INR'),
        ];

        return $this->sendResponse($config, 'Razorpay configuration retrieved successfully.');
    }
}
