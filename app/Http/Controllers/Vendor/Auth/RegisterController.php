<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBankAccount;
use App\Mail\VendorRegistrationPending;

class RegisterController extends Controller
{
    /**
     * Show the vendor registration form
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('vendor.auth.register');
    }

    /**
     * Handle vendor registration request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'required|string|max:20',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string|max:1000',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            // Bank account details
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|min:10|max:20',
            'confirm_account_number' => 'required|string|same:account_number',
            'ifsc_code' => 'required|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_name' => 'nullable|string|max:100',
            'branch_name' => 'nullable|string|max:100',
            'account_type' => 'nullable|string|in:savings,current',
            'terms' => 'required|accepted',
        ], [
            'account_holder_name.required' => 'Account holder name is required.',
            'account_number.required' => 'Account number is required.',
            'account_number.min' => 'Account number must be at least 10 digits.',
            'account_number.max' => 'Account number cannot exceed 20 digits.',
            'confirm_account_number.same' => 'Account numbers do not match.',
            'ifsc_code.required' => 'IFSC code is required.',
            'ifsc_code.size' => 'IFSC code must be exactly 11 characters.',
            'ifsc_code.regex' => 'Invalid IFSC code format. It should be like ABCD0123456.',
        ]);

        DB::beginTransaction();

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile_number' => $request->mobile_number,
                'user_role' => 'vendor',
                'is_approved' => false,
            ]);

            // Create the vendor profile
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $request->store_name,
                'store_slug' => Str::slug($request->store_name),
                'store_description' => $request->store_description,
                'business_email' => $request->business_email ?? $request->email,
                'business_phone' => $request->business_phone ?? $request->mobile_number,
                'business_address' => $request->business_address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'status' => Vendor::STATUS_PENDING,
            ]);

            // Create the vendor bank account
            VendorBankAccount::create([
                'vendor_id' => $vendor->id,
                'account_holder_name' => $request->account_holder_name,
                'account_number' => $request->account_number,
                'ifsc_code' => strtoupper($request->ifsc_code),
                'bank_name' => $request->bank_name,
                'branch_name' => $request->branch_name,
                'account_type' => $request->account_type ?? 'savings',
                'is_primary' => true,
                'is_verified' => false,
                'fund_account_status' => 'pending',
            ]);

            DB::commit();

            // Send registration pending email
            try {
                Mail::to($user->email)->send(new VendorRegistrationPending($user, $vendor));
            } catch (\Exception $e) {
                // Log the error but don't fail the registration
                Log::error('Failed to send vendor registration email: ' . $e->getMessage());
            }

            // Log the user in
            Auth::login($user);

            // Redirect to pending page
            return redirect()->route('vendor.pending')->with('success', 'Your vendor registration has been submitted successfully. Please wait for admin approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->withErrors([
                'error' => 'Registration failed. Please try again. ' . $e->getMessage(),
            ]);
        }
    }
}
