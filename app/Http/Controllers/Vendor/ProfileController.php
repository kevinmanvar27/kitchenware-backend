<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Vendor;
use App\Traits\LogsActivity;

class ProfileController extends Controller
{
    use LogsActivity;
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->getActiveVendor();
    }

    /**
     * Show the vendor profile index page.
     */
    public function index()
    {
        $user = Auth::user();
        $vendor = $this->getVendor();
        
        return view('vendor.profile.index', compact('user', 'vendor'));
    }

    /**
     * Show the vendor profile.
     */
    public function show()
    {
        $user = Auth::user();
        $vendor = $this->getVendor();
        
        return view('vendor.profile.index', compact('user', 'vendor'));
    }

    /**
     * Show the store settings page.
     */
    public function storeSettings()
    {
        $user = Auth::user();
        $vendor = $this->getVendor();
        $settings = $vendor->store_settings ?? [];
        
        return view('vendor.profile.store', compact('user', 'vendor', 'settings'));
    }

    /**
     * Show the bank details page.
     */
    public function showBankDetails()
    {
        $user = Auth::user();
        $vendor = $this->getVendor();
        
        return view('vendor.profile.bank-details', compact('user', 'vendor'));
    }

    /**
     * Update the vendor profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $vendor = $this->getVendor();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile_number' => 'nullable|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string|max:1000',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
        ]);

        // Update user info
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
        ]);

        // Update vendor info
        $vendor->update([
            'store_name' => $request->store_name,
            'store_description' => $request->store_description,
            'business_email' => $request->business_email,
            'business_phone' => $request->business_phone,
            'business_address' => $request->business_address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
        ]);
        
        // Log activity
        $this->logVendorActivity($vendor->id, 'updated', 'Updated profile information', $vendor);

        return redirect()->route('vendor.profile.index')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the vendor address.
     */
    public function updateAddress(Request $request)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $vendor->update([
            'business_address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
        ]);

        return redirect()->route('vendor.profile.index')->with('success', 'Address updated successfully.');
    }

    /**
     * Update the vendor password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        // Log activity (don't log actual password)
        $vendor = $this->getVendor();
        if ($vendor) {
            $this->logVendorActivity($vendor->id, 'updated', 'Changed account password');
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Password changed successfully.');
    }

    /**
     * Update store settings.
     */
    public function updateStoreSettings(Request $request)
    {
        $vendor = $this->getVendor();
        
        $request->validate([
            'tagline' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'return_policy' => 'nullable|string',
            'shipping_policy' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'banner_image_url' => 'nullable|url|max:500',
            'banner_redirect_url' => 'nullable|url|max:500',
        ]);

        // Handle banner image URL (external URL)
        if ($request->filled('banner_image_url')) {
            // If banner_image_url is provided, clear the uploaded banner file
            if ($vendor->store_banner) {
                Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
            }
            $vendor->update([
                'banner_image_url' => $request->banner_image_url,
                'store_banner' => null,
            ]);
        }
        // Handle banner upload (file)
        elseif ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($vendor->store_banner) {
                Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
            }
            
            $file = $request->file('banner');
            $filename = time() . '_banner_' . $file->getClientOriginalName();
            $file->storeAs('vendor', $filename, 'public');
            $vendor->update([
                'store_banner' => $filename,
                'banner_image_url' => null,
            ]);
        }

        // Handle banner redirect URL
        if ($request->filled('banner_redirect_url')) {
            $vendor->update([
                'banner_redirect_url' => $request->banner_redirect_url,
            ]);
        }

        // Get all settings from request
        $settings = $request->except(['_token', '_method', 'banner', 'banner_image_url', 'banner_redirect_url']);
        
        // Sanitize HTML content for policy fields to prevent XSS
        $htmlFields = ['return_policy', 'shipping_policy', 'terms_conditions'];
        foreach ($htmlFields as $field) {
            if (isset($settings[$field])) {
                $settings[$field] = $this->sanitizeHtml($settings[$field]);
            }
        }
        
        $vendor->update([
            'store_settings' => $settings,
        ]);
        
        // Log activity
        $this->logVendorActivity($vendor->id, 'updated', 'Updated store settings', $vendor);

        return redirect()->route('vendor.profile.store')->with('success', 'Store settings updated successfully.');
    }
    
    /**
     * Sanitize HTML content to prevent XSS attacks while allowing safe formatting.
     */
    private function sanitizeHtml($html)
    {
        // Allow only safe HTML tags and attributes
        $allowedTags = '<p><br><strong><b><em><i><u><s><h1><h2><h3><h4><h5><h6><ul><ol><li><a><blockquote><table><thead><tbody><tr><th><td>';
        
        // Strip tags that are not allowed
        $html = strip_tags($html, $allowedTags);
        
        // Remove potentially dangerous attributes
        $html = preg_replace('/<(\w+)[^>]*\son\w+\s*=\s*["\'][^"\']*["\'][^>]*>/i', '<$1>', $html);
        
        // Only allow href attribute on <a> tags and remove javascript: protocol
        $html = preg_replace('/<a[^>]*href\s*=\s*["\']javascript:[^"\']*["\'][^>]*>/i', '<a>', $html);
        
        return $html;
    }

    /**
     * Update the vendor's avatar.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        // Store new avatar
        $file = $request->file('avatar');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('avatars', $filename, 'public');

        $user->update(['avatar' => $filename]);

        return redirect()->route('vendor.profile.index')->with('success', 'Avatar updated successfully.');
    }

    /**
     * Remove the vendor's avatar.
     */
    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->update(['avatar' => null]);
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Avatar removed successfully.');
    }

    /**
     * Update the store logo.
     */
    public function updateStoreLogo(Request $request)
    {
        $request->validate([
            'store_logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $vendor = $this->getVendor();

        // Delete old logo if exists
        if ($vendor->store_logo) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_logo);
            Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_logo);
        }

        // Store new logo in vendor-specific folder
        $file = $request->file('store_logo');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $file->storeAs('vendor/' . $vendor->id, $filename, 'public');

        $vendor->update(['store_logo' => $filename]);

        return redirect()->route('vendor.profile.index')->with('success', 'Store logo updated successfully.');
    }

    /**
     * Remove the store logo.
     */
    public function removeStoreLogo()
    {
        $vendor = $this->getVendor();

        if ($vendor->store_logo) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_logo);
            Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_logo);
            $vendor->update(['store_logo' => null]);
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Store logo removed successfully.');
    }

    /**
     * Update the store banner.
     */
    public function updateStoreBanner(Request $request)
    {
        $request->validate([
            'store_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $vendor = $this->getVendor();

        // Delete old banner if exists
        if ($vendor->store_banner) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
            Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_banner);
        }

        // Store new banner in vendor-specific folder
        $file = $request->file('store_banner');
        $filename = time() . '_banner_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $file->storeAs('vendor/' . $vendor->id, $filename, 'public');

        $vendor->update(['store_banner' => $filename]);

        return redirect()->route('vendor.profile.index')->with('success', 'Store banner updated successfully.');
    }

    /**
     * Remove the store banner.
     */
    public function removeStoreBanner()
    {
        $vendor = $this->getVendor();

        if ($vendor->store_banner) {
            Storage::disk('public')->delete('vendor/' . $vendor->store_banner);
            Storage::disk('public')->delete('vendor/' . $vendor->id . '/' . $vendor->store_banner);
            $vendor->update(['store_banner' => null]);
        }

        return redirect()->route('vendor.profile.index')->with('success', 'Store banner removed successfully.');
    }

    /**
     * Update bank details.
     */
    public function updateBankDetails(Request $request)
    {
        $vendor = $this->getVendor();
        $bankAccount = $vendor->primaryBankAccount;
        
        // Build validation rules - account confirmation required
        $validationRules = [
            'bank_name' => 'required|string|max:255',
            'bank_ifsc_code' => 'required|string|max:20',
            'bank_account_holder_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'account_type' => 'required|in:savings,current',
            'confirm_account_number' => 'required|same:bank_account_number',
        ];
        
        // Account number is always required for new accounts
        // For existing accounts, it's only required if they're changing it
        if (!$bankAccount) {
            $validationRules['bank_account_number'] = 'required|string|max:50';
        } else {
            $validationRules['bank_account_number'] = 'required|string|max:50';
        }
        
        $request->validate($validationRules);

        // Check if vendor has a primary bank account
        if ($bankAccount) {
            // Update existing bank account
            $bankAccount->update([
                'account_holder_name' => $request->bank_account_holder_name,
                'account_number' => $request->bank_account_number,
                'ifsc_code' => $request->bank_ifsc_code,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'account_type' => $request->account_type,
                // Reset RazorpayX details if IFSC code changes
                'razorpay_fund_account_id' => $request->bank_ifsc_code !== $bankAccount->ifsc_code ? null : $bankAccount->razorpay_fund_account_id,
                'razorpay_contact_id' => $request->bank_ifsc_code !== $bankAccount->ifsc_code ? null : $bankAccount->razorpay_contact_id,
                'fund_account_status' => $request->bank_ifsc_code !== $bankAccount->ifsc_code ? 'pending' : $bankAccount->fund_account_status,
            ]);
        } else {
            // Create new bank account
            \App\Models\VendorBankAccount::create([
                'vendor_id' => $vendor->id,
                'account_holder_name' => $request->bank_account_holder_name,
                'account_number' => $request->bank_account_number,
                'ifsc_code' => $request->bank_ifsc_code,
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'account_type' => $request->account_type,
                'is_primary' => true,
                'fund_account_status' => 'pending',
            ]);
        }
        
        // Log activity (don't log sensitive account details)
        $this->logVendorActivity($vendor->id, 'updated', "Updated bank details for {$request->bank_name}");

        return redirect()->route('vendor.profile.bank-details')->with('success', 'Bank details updated successfully.');
    }

    /**
     * Update social links.
     */
    public function updateSocialLinks(Request $request)
    {
        $request->validate([
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $vendor = $this->getVendor();

        $vendor->update([
            'social_links' => [
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'instagram' => $request->instagram,
                'linkedin' => $request->linkedin,
                'youtube' => $request->youtube,
                'website' => $request->website,
            ],
        ]);

        return redirect()->route('vendor.profile.index')->with('success', 'Social links updated successfully.');
    }
}
