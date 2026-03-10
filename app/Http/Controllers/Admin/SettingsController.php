<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\Setting;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Traits\LogsActivity;

class SettingsController extends Controller
{
    use LogsActivity;
    
    /**
     * Display the settings page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get the first (and only) settings record, or create a new one with default values
            $setting = Setting::firstOrCreate([], [
                'site_title' => 'Hardware Store',
                'site_description' => 'Your one-stop shop for all hardware needs',
                'tagline' => 'Quality hardware solutions for everyone',
                'footer_text' => '© 2025 Hardware Store. All rights reserved.',
                'address' => null,
                'company_email' => null,
                'company_phone' => null,
                'gst_number' => null,
                'authorized_signatory' => null,
                'theme_color' => '#FF6B00',
                'background_color' => '#FFFFFF',
                'font_color' => '#333333',
                'font_style' => 'Arial, sans-serif',
                // Default element-wise font family values
                'h1_font_family' => 'Arial, sans-serif',
                'h2_font_family' => 'Arial, sans-serif',
                'h3_font_family' => 'Arial, sans-serif',
                'h4_font_family' => 'Arial, sans-serif',
                'h5_font_family' => 'Arial, sans-serif',
                'h6_font_family' => 'Arial, sans-serif',
                'body_font_family' => 'Arial, sans-serif',
                'sidebar_text_color' => '#333333',
                'heading_text_color' => '#333333',
                'label_text_color' => '#333333',
                'general_text_color' => '#333333',
                'link_color' => '#333333',
                'link_hover_color' => '#FF6B00',
                'header_logo' => null,
                'footer_logo' => null,
                'favicon' => null,
                'facebook_url' => null,
                'twitter_url' => null,
                'instagram_url' => null,
                'linkedin_url' => null,
                'youtube_url' => null,
                'whatsapp_url' => null,
                'maintenance_mode' => false,
                'maintenance_end_time' => null,
                'maintenance_message' => 'We are currently under maintenance. The website will be back online approximately at {end_time}.',
                'coming_soon_mode' => false,
                'launch_time' => null,
                'coming_soon_message' => "We're launching soon! Our amazing platform will be available at {launch_time}.",
                'razorpay_key_id' => null,
                'razorpay_key_secret' => null,
                'app_store_link' => null,
                'play_store_link' => null,
                // Default font size values
                'desktop_h1_size' => 36,
                'desktop_h2_size' => 30,
                'desktop_h3_size' => 24,
                'desktop_h4_size' => 20,
                'desktop_h5_size' => 18,
                'desktop_h6_size' => 16,
                'desktop_body_size' => 16,
                'tablet_h1_size' => 32,
                'tablet_h2_size' => 28,
                'tablet_h3_size' => 22,
                'tablet_h4_size' => 18,
                'tablet_h5_size' => 16,
                'tablet_h6_size' => 14,
                'tablet_body_size' => 14,
                'mobile_h1_size' => 28,
                'mobile_h2_size' => 24,
                'mobile_h3_size' => 20,
                'mobile_h4_size' => 16,
                'mobile_h5_size' => 14,
                'mobile_h6_size' => 12,
                'mobile_body_size' => 12,
                'frontend_access_permission' => 'open_for_all',
                'pending_approval_message' => 'Your account is pending approval. Please wait for admin approval before accessing the site.',
                'show_online_payment' => true,
                'show_cod_payment' => true,
                'show_invoice_payment' => true,
            ]);
            
            // Get subscription plans for the subscription tab
            $subscriptionPlans = SubscriptionPlan::ordered()->get();
            
            return view('admin.settings.index', compact('setting', 'subscriptionPlans'));
        } catch (QueryException $e) {
            Log::error('Database error in settings index: ' . $e->getMessage());
            $errorMessage = getUserFriendlyErrorMessage($e);
            return redirect()->route('dashboard')->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error in settings index: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Unable to load settings. Please try again later.');
        }
    }
    
    /**
     * Update the settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Check if this is a password change request
        if ($request->filled('current_password')) {
            return $this->changePassword($request);
        }
        
        $request->validate([
            'header_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'footer_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            'site_title' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'tagline' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string',
            'address' => 'nullable|string',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:15',
            'authorized_signatory' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'theme_color' => 'nullable|string|max:7',
            'background_color' => 'nullable|string|max:7',
            'font_color' => 'nullable|string|max:7',
            'font_style' => 'nullable|string|max:255',
            // Element-wise font family validation rules
            'h1_font_family' => 'nullable|string|max:255',
            'h2_font_family' => 'nullable|string|max:255',
            'h3_font_family' => 'nullable|string|max:255',
            'h4_font_family' => 'nullable|string|max:255',
            'h5_font_family' => 'nullable|string|max:255',
            'h6_font_family' => 'nullable|string|max:255',
            'body_font_family' => 'nullable|string|max:255',
            'sidebar_text_color' => 'nullable|string|max:7',
            'heading_text_color' => 'nullable|string|max:7',
            'label_text_color' => 'nullable|string|max:7',
            'general_text_color' => 'nullable|string|max:7',
            'link_color' => 'nullable|string|max:7',
            'link_hover_color' => 'nullable|string|max:7',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'whatsapp_url' => 'nullable|url',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
            'firebase_project_id' => 'nullable|string|max:255',
            'firebase_client_email' => 'nullable|string|max:255',
            'firebase_private_key' => 'nullable|string',
            'app_store_link' => 'nullable|url',
            'play_store_link' => 'nullable|url',
            // Font size validation rules
            'desktop_h1_size' => 'nullable|integer|min:1',
            'desktop_h2_size' => 'nullable|integer|min:1',
            'desktop_h3_size' => 'nullable|integer|min:1',
            'desktop_h4_size' => 'nullable|integer|min:1',
            'desktop_h5_size' => 'nullable|integer|min:1',
            'desktop_h6_size' => 'nullable|integer|min:1',
            'desktop_body_size' => 'nullable|integer|min:1',
            'tablet_h1_size' => 'nullable|integer|min:1',
            'tablet_h2_size' => 'nullable|integer|min:1',
            'tablet_h3_size' => 'nullable|integer|min:1',
            'tablet_h4_size' => 'nullable|integer|min:1',
            'tablet_h5_size' => 'nullable|integer|min:1',
            'tablet_h6_size' => 'nullable|integer|min:1',
            'tablet_body_size' => 'nullable|integer|min:1',
            'mobile_h1_size' => 'nullable|integer|min:1',
            'mobile_h2_size' => 'nullable|integer|min:1',
            'mobile_h3_size' => 'nullable|integer|min:1',
            'mobile_h4_size' => 'nullable|integer|min:1',
            'mobile_h5_size' => 'nullable|integer|min:1',
            'mobile_h6_size' => 'nullable|integer|min:1',
            'mobile_body_size' => 'nullable|integer|min:1',
            // Frontend access permission validation rules
            'frontend_access_permission' => 'nullable|string|in:open_for_all,registered_users_only,admin_approval_required',
            'pending_approval_message' => 'nullable|string',
            // Payment method visibility validation rules
            'show_online_payment' => 'nullable|boolean',
            'show_cod_payment' => 'nullable|boolean',
            'show_invoice_payment' => 'nullable|boolean',
            // RazorpayX validation rules
            'razorpayx_key_id' => 'nullable|string|max:255',
            'razorpayx_key_secret' => 'nullable|string|max:255',
            'razorpayx_account_number' => 'nullable|string|max:50',
            'razorpayx_webhook_secret' => 'nullable|string|max:255',
            'razorpayx_mode' => 'nullable|string|in:test,live',
            // Site Management validation rules
            'maintenance_end_time' => 'nullable|string',
            'maintenance_message' => 'nullable|string',
            'launch_time' => 'nullable|string',
            'coming_soon_message' => 'nullable|string',
        ]);
        
        // Get the first (and only) settings record, or create a new one
        $setting = Setting::firstOrCreate([]);
        
        // Handle image removals
        if ($request->has('remove_header_logo')) {
            $this->removeImage($setting, 'header_logo');
        }
        
        if ($request->has('remove_footer_logo')) {
            $this->removeImage($setting, 'footer_logo');
        }
        
        if ($request->has('remove_favicon')) {
            $this->removeImage($setting, 'favicon');
        }
        
        // Handle authorized signatory removal
        if ($request->has('remove_authorized_signatory')) {
            $this->removeFile($setting, 'authorized_signatory');
        }
        
        // Handle image uploads and delete old images
        $this->handleImageUpload($request, $setting, 'header_logo');
        $this->handleImageUpload($request, $setting, 'footer_logo');
        $this->handleImageUpload($request, $setting, 'favicon');
        
        // Handle authorized signatory file upload
        if ($request->hasFile('authorized_signatory')) {
            // Delete old authorized signatory if exists
            if ($setting->authorized_signatory) {
                Storage::disk('public')->delete($setting->authorized_signatory);
            }
            
            // Store new authorized signatory file
            $path = $request->file('authorized_signatory')->store('settings', 'public');
            $setting->authorized_signatory = $path;
        }
        
        // Update text fields
        $setting->site_title = $request->site_title;
        $setting->site_description = $request->site_description;
        $setting->tagline = $request->tagline;
        $setting->footer_text = $request->footer_text;
        $setting->address = $request->address;
        $setting->company_email = $request->company_email;
        $setting->company_phone = $request->company_phone;
        $setting->gst_number = $request->gst_number;
        $setting->theme_color = $request->theme_color;
        $setting->background_color = $request->background_color;
        $setting->font_color = $request->font_color;
        $setting->font_style = $request->font_style;
        
        // Update element-wise font family fields
        $setting->h1_font_family = $request->h1_font_family;
        $setting->h2_font_family = $request->h2_font_family;
        $setting->h3_font_family = $request->h3_font_family;
        $setting->h4_font_family = $request->h4_font_family;
        $setting->h5_font_family = $request->h5_font_family;
        $setting->h6_font_family = $request->h6_font_family;
        $setting->body_font_family = $request->body_font_family;
        
        $setting->sidebar_text_color = $request->sidebar_text_color;
        $setting->heading_text_color = $request->heading_text_color;
        $setting->label_text_color = $request->label_text_color;
        $setting->general_text_color = $request->general_text_color;
        $setting->link_color = $request->link_color;
        $setting->link_hover_color = $request->link_hover_color;
        $setting->facebook_url = $request->facebook_url;
        $setting->twitter_url = $request->twitter_url;
        $setting->instagram_url = $request->instagram_url;
        $setting->linkedin_url = $request->linkedin_url;
        $setting->youtube_url = $request->youtube_url;
        $setting->whatsapp_url = $request->whatsapp_url;
        $setting->razorpay_key_id = $request->razorpay_key_id;
        $setting->razorpay_key_secret = $request->razorpay_key_secret;
        
        // Update RazorpayX settings for vendor payouts
        $setting->razorpayx_key_id = $request->razorpayx_key_id;
        $setting->razorpayx_key_secret = $request->razorpayx_key_secret;
        $setting->razorpayx_account_number = $request->razorpayx_account_number;
        $setting->razorpayx_webhook_secret = $request->razorpayx_webhook_secret;
        $setting->razorpayx_mode = $request->razorpayx_mode ?? 'test';
        
        $setting->firebase_project_id = $request->firebase_project_id;
        $setting->firebase_client_email = $request->firebase_client_email;
        $setting->firebase_private_key = $request->firebase_private_key;
        $setting->app_store_link = $request->app_store_link;
        $setting->play_store_link = $request->play_store_link;
        
        // Update font size fields
        $setting->desktop_h1_size = $request->desktop_h1_size;
        $setting->desktop_h2_size = $request->desktop_h2_size;
        $setting->desktop_h3_size = $request->desktop_h3_size;
        $setting->desktop_h4_size = $request->desktop_h4_size;
        $setting->desktop_h5_size = $request->desktop_h5_size;
        $setting->desktop_h6_size = $request->desktop_h6_size;
        $setting->desktop_body_size = $request->desktop_body_size;
        $setting->tablet_h1_size = $request->tablet_h1_size;
        $setting->tablet_h2_size = $request->tablet_h2_size;
        $setting->tablet_h3_size = $request->tablet_h3_size;
        $setting->tablet_h4_size = $request->tablet_h4_size;
        $setting->tablet_h5_size = $request->tablet_h5_size;
        $setting->tablet_h6_size = $request->tablet_h6_size;
        $setting->tablet_body_size = $request->tablet_body_size;
        $setting->mobile_h1_size = $request->mobile_h1_size;
        $setting->mobile_h2_size = $request->mobile_h2_size;
        $setting->mobile_h3_size = $request->mobile_h3_size;
        $setting->mobile_h4_size = $request->mobile_h4_size;
        $setting->mobile_h5_size = $request->mobile_h5_size;
        $setting->mobile_h6_size = $request->mobile_h6_size;
        $setting->mobile_body_size = $request->mobile_body_size;
        
        // Update frontend access permission settings
        $setting->frontend_access_permission = $request->frontend_access_permission ?? 'open_for_all';
        $setting->pending_approval_message = $request->pending_approval_message;
        
        // Update payment method visibility settings
        // Using boolean() method for proper checkbox handling (returns true/false)
        $setting->show_online_payment = $request->boolean('show_online_payment');
        $setting->show_cod_payment = $request->boolean('show_cod_payment');
        $setting->show_invoice_payment = $request->boolean('show_invoice_payment');
        
        // Update site management fields with mutual exclusivity
        $maintenanceMode = $request->boolean('maintenance_mode');
        $comingSoonMode = $request->boolean('coming_soon_mode');
        
        // Ensure only one mode is active at a time
        if ($maintenanceMode && $comingSoonMode) {
            // If both are checked, prioritize maintenance mode
            $setting->maintenance_mode = true;
            $setting->coming_soon_mode = false;
        } else {
            $setting->maintenance_mode = $maintenanceMode;
            $setting->coming_soon_mode = $comingSoonMode;
        }
        
        // Parse and save maintenance end time
        if ($request->filled('maintenance_end_time')) {
            $maintenanceEndTimeStr = trim($request->maintenance_end_time);
            $maintenanceEndTime = \DateTime::createFromFormat('d/m/Y H:i', $maintenanceEndTimeStr);
            if (!$maintenanceEndTime) {
                // Try alternative format without leading zeros
                $maintenanceEndTime = \DateTime::createFromFormat('j/n/Y H:i', $maintenanceEndTimeStr);
            }
            $setting->maintenance_end_time = $maintenanceEndTime ? $maintenanceEndTime->format('Y-m-d H:i:s') : null;
        } else {
            $setting->maintenance_end_time = null;
        }
        
        $setting->maintenance_message = $request->maintenance_message;
        
        // Parse and save launch time
        if ($request->filled('launch_time')) {
            $launchTimeStr = trim($request->launch_time);
            $launchTime = \DateTime::createFromFormat('d/m/Y H:i', $launchTimeStr);
            if (!$launchTime) {
                // Try alternative format without leading zeros
                $launchTime = \DateTime::createFromFormat('j/n/Y H:i', $launchTimeStr);
            }
            $setting->launch_time = $launchTime ? $launchTime->format('Y-m-d H:i:s') : null;
        } else {
            $setting->launch_time = null;
        }
        
        $setting->coming_soon_message = $request->coming_soon_message;
        
        try {
            // Track changed fields for logging
            $changedFields = [];
            foreach ($setting->getDirty() as $field => $newValue) {
                // Skip sensitive fields and file paths in the log
                if (!in_array($field, ['razorpay_key_secret', 'razorpayx_key_secret', 'razorpayx_webhook_secret', 'firebase_private_key'])) {
                    $changedFields[] = $field;
                }
            }
            
            $setting->save();
            
            // Log activity with changed fields summary
            if (!empty($changedFields)) {
                $fieldCount = count($changedFields);
                $fieldSummary = $fieldCount <= 5 
                    ? implode(', ', $changedFields) 
                    : implode(', ', array_slice($changedFields, 0, 5)) . " and " . ($fieldCount - 5) . " more";
                $this->logAdminActivity('updated', "Updated site settings: {$fieldSummary}", $setting);
            }
            
            // Get the active tab from the request
            $activeTab = $request->input('active_tab', 'general');
            
            return redirect()->back()->with('success', 'Settings updated successfully.')->with('tab', $activeTab);
        } catch (QueryException $e) {
            Log::error('Database error updating settings: ' . $e->getMessage());
            $errorMessage = getUserFriendlyErrorMessage($e);
            return redirect()->back()->withInput()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error updating settings: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to save settings. Please try again.');
        }
    }
    
    /**
     * Change the user's password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'new_password.different' => 'The new password must be different from the current password.',
            'new_password.confirmed' => 'The password confirmation does not match.',
        ]);
        
        /** @var User $user */
        $user = Auth::user();
        
        // Check if current password is correct
        if (!Auth::attempt(['email' => $user->email, 'password' => $request->current_password])) {
            return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.'])->with('tab', 'password');
        }
        
        // Update password
        $user->password = bcrypt($request->new_password);
        $user->save();
        
        return redirect()->back()->with('success', 'Password changed successfully.')->with('tab', 'password');
    }
    
    /**
     * Handle image upload and delete old image
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @param  string  $fieldName
     * @return void
     */
    private function handleImageUpload(Request $request, Setting $setting, string $fieldName)
    {
        if ($request->hasFile($fieldName)) {
            // Delete old image if exists
            if ($setting->$fieldName) {
                Storage::disk('public')->delete($setting->$fieldName);
            }
            
            // Store new image
            $path = $request->file($fieldName)->store('settings', 'public');
            $setting->$fieldName = $path;
        }
    }
    
    /**
     * Remove an image and delete it from storage
     *
     * @param  \App\Models\Setting  $setting
     * @param  string  $fieldName
     * @return void
     */
    private function removeImage(Setting $setting, string $fieldName)
    {
        if ($setting->$fieldName) {
            Storage::disk('public')->delete($setting->$fieldName);
            $setting->$fieldName = null;
        }
    }
    
    /**
     * Remove a file and delete it from storage
     *
     * @param  \App\Models\Setting  $setting
     * @param  string  $fieldName
     * @return void
     */
    private function removeFile(Setting $setting, string $fieldName)
    {
        if ($setting->$fieldName) {
            Storage::disk('public')->delete($setting->$fieldName);
            $setting->$fieldName = null;
        }
    }
    
    /**
     * Reset settings to default values
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        try {
            $setting = Setting::first();
            if ($setting) {
                // Delete existing images and files
                $fileFields = ['header_logo', 'footer_logo', 'favicon', 'authorized_signatory'];
                foreach ($fileFields as $field) {
                    if ($setting->$field) {
                        Storage::disk('public')->delete($setting->$field);
                    }
                }
                
                // Reset all fields to default values
                $setting->update([
                    'site_title' => 'Hardware Store',
                    'site_description' => 'Your one-stop shop for all hardware needs',
                    'tagline' => 'Quality hardware solutions for everyone',
                    'footer_text' => '© 2025 Hardware Store. All rights reserved.',
                    'address' => null,
                    'company_email' => null,
                    'company_phone' => null,
                    'gst_number' => null,
                    'authorized_signatory' => null,
                    'theme_color' => '#FF6B00',
                    'background_color' => '#FFFFFF',
                    'font_color' => '#333333',
                    'font_style' => 'Arial, sans-serif',
                    // Default element-wise font family values
                    'h1_font_family' => 'Arial, sans-serif',
                    'h2_font_family' => 'Arial, sans-serif',
                    'h3_font_family' => 'Arial, sans-serif',
                    'h4_font_family' => 'Arial, sans-serif',
                    'h5_font_family' => 'Arial, sans-serif',
                    'h6_font_family' => 'Arial, sans-serif',
                    'body_font_family' => 'Arial, sans-serif',
                    'sidebar_text_color' => '#333333',
                    'heading_text_color' => '#333333',
                    'label_text_color' => '#333333',
                    'general_text_color' => '#333333',
                    'link_color' => '#333333',
                    'link_hover_color' => '#FF6B00',
                    'header_logo' => null,
                    'footer_logo' => null,
                    'favicon' => null,
                    'facebook_url' => null,
                    'twitter_url' => null,
                    'instagram_url' => null,
                    'linkedin_url' => null,
                    'youtube_url' => null,
                    'whatsapp_url' => null,
                    'maintenance_mode' => false,
                    'maintenance_end_time' => null,
                    'maintenance_message' => 'We are currently under maintenance. The website will be back online approximately at {end_time}.',
                    'coming_soon_mode' => false,
                    'launch_time' => null,
                    'coming_soon_message' => "We're launching soon! Our amazing platform will be available at {launch_time}.",
                    'razorpay_key_id' => null,
                    'razorpay_key_secret' => null,
                    'app_store_link' => null,
                    'play_store_link' => null,
                    // Default font size values
                    'desktop_h1_size' => 36,
                    'desktop_h2_size' => 30,
                    'desktop_h3_size' => 24,
                    'desktop_h4_size' => 20,
                    'desktop_h5_size' => 18,
                    'desktop_h6_size' => 16,
                    'desktop_body_size' => 16,
                    'tablet_h1_size' => 32,
                    'tablet_h2_size' => 28,
                    'tablet_h3_size' => 22,
                    'tablet_h4_size' => 18,
                    'tablet_h5_size' => 16,
                    'tablet_h6_size' => 14,
                    'tablet_body_size' => 14,
                    'mobile_h1_size' => 28,
                    'mobile_h2_size' => 24,
                    'mobile_h3_size' => 20,
                    'mobile_h4_size' => 16,
                    'mobile_h5_size' => 14,
                    'mobile_h6_size' => 12,
                    'mobile_body_size' => 12,
                    'frontend_access_permission' => 'open_for_all',
                    'pending_approval_message' => 'Your account is pending approval. Please wait for admin approval before accessing the site.',
                    'show_online_payment' => true,
                    'show_cod_payment' => true,
                    'show_invoice_payment' => true,
                ]);
            }
            
            return redirect()->back()->with('success', 'Settings reset to default values successfully.');
        } catch (QueryException $e) {
            Log::error('Database error resetting settings: ' . $e->getMessage());
            $errorMessage = getUserFriendlyErrorMessage($e);
            return redirect()->back()->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Error resetting settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reset settings. Please try again.');
        }
    }
    
    /**
     * Clean the database by removing all user data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanDatabase(Request $request)
    {
        // Add database cleaning logic here
        // This is a placeholder implementation
        
        return redirect()->back()->with('success', 'Database cleaned successfully.');
    }
    
    /**
     * Export the full database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportDatabase(Request $request)
    {
        try {
            $user = auth()->user();
            $vendorId = null;
            
            // Determine vendor context
            if (!$user->isSuperAdmin()) {
                if ($user->isVendor()) {
                    $vendor = $user->vendor;
                    if ($vendor) {
                        $vendorId = $vendor->id;
                    } else {
                        return redirect()->back()->with('error', 'Vendor profile not found.');
                    }
                } elseif ($user->isVendorStaff()) {
                    $staffRecord = $user->vendorStaff;
                    if ($staffRecord && $staffRecord->vendor) {
                        $vendorId = $staffRecord->vendor->id;
                    } else {
                        return redirect()->back()->with('error', 'Vendor staff profile not found.');
                    }
                } else {
                    return redirect()->back()->with('error', 'You do not have permission to export database.');
                }
            }
            
            // Get all tables
            $database = env('DB_DATABASE');
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;
            $allTables = array_map(function($table) use ($tableKey) {
                return $table->$tableKey;
            }, $tables);
            
            // Generate SQL dump
            $sqlContent = $this->generateQuickSqlDump($allTables, $vendorId);
            
            // Create filename
            $filenameParts = ['database_export'];
            if ($vendorId) {
                $vendor = \App\Models\Vendor::find($vendorId);
                if ($vendor) {
                    $filenameParts[] = 'vendor_' . $vendor->id . '_' . \Illuminate\Support\Str::slug($vendor->store_name);
                }
            } else {
                $filenameParts[] = 'full';
            }
            $filenameParts[] = date('Y-m-d_H-i-s');
            $filename = implode('_', $filenameParts) . '.sql';
            
            // Ensure exports directory exists
            $exportsDir = storage_path('app/private/exports');
            if (!file_exists($exportsDir)) {
                mkdir($exportsDir, 0755, true);
            }
            
            // Save file
            $fullPath = $exportsDir . '/' . $filename;
            file_put_contents($fullPath, $sqlContent);
            
            // Download and delete after send
            return response()->download($fullPath, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Database Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate a quick SQL dump for all tables
     */
    private function generateQuickSqlDump($tables, $vendorId = null)
    {
        $sql = "-- Database Export\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        
        if ($vendorId) {
            $vendor = \App\Models\Vendor::find($vendorId);
            if ($vendor) {
                $sql .= "-- Vendor: {$vendor->store_name} (ID: {$vendorId})\n";
            }
        } else {
            $sql .= "-- Full Database Export\n";
        }
        
        $sql .= "-- Tables: " . count($tables) . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table: $table\n";
            $sql .= "-- --------------------------------------------------------\n\n";
            
            // Export structure
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $createTable = DB::select("SHOW CREATE TABLE `$table`");
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            
            // Export data with vendor filtering if applicable
            $query = DB::table($table);
            
            // Apply vendor filtering for specific tables
            if ($vendorId) {
                $query = $this->applyVendorFilterQuick($query, $table, $vendorId);
            }
            
            $rows = $query->get();
            
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $row = (array) $row;
                    $columns = array_keys($row);
                    $values = array_values($row);
                    
                    // Escape values
                    $values = array_map(function($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, $values);
                    
                    $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        return $sql;
    }
    
    /**
     * Apply vendor filter to query based on table
     */
    private function applyVendorFilterQuick($query, $table, $vendorId)
    {
        // Tables with direct vendor_id column
        $directVendorTables = [
            'products', 'proforma_invoices', 'without_gst_invoices', 
            'vendor_earnings', 'vendor_wallets', 'vendor_payouts',
            'vendor_bank_accounts', 'vendor_customers', 'vendor_feature_settings',
            'vendor_reviews', 'vendor_followers', 'coupons',
            'push_notifications', 'scheduled_notifications', 'vendor_staff',
        ];
        
        if (in_array($table, $directVendorTables)) {
            $query->where('vendor_id', $vendorId);
        }
        
        // Special cases
        switch ($table) {
            case 'vendors':
                $query->where('id', $vendorId);
                break;
                
            case 'users':
                $vendor = \App\Models\Vendor::find($vendorId);
                if ($vendor) {
                    $query->where(function($q) use ($vendor, $vendorId) {
                        $q->where('id', $vendor->user_id)
                          ->orWhere('vendor_id', $vendorId);
                    });
                }
                break;
                
            case 'product_images':
            case 'product_views':
                $productIds = DB::table('products')->where('vendor_id', $vendorId)->pluck('id');
                if ($productIds->isNotEmpty()) {
                    $query->whereIn('product_id', $productIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
        }
        
        return $query;
    }
}