<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\VendorStaff;

class LoginController extends Controller
{
    /**
     * Show the vendor login form
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLoginForm()
    {
        // If user is already authenticated as vendor or vendor staff, redirect to dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isVendor() || $user->isVendorStaff()) {
                return redirect()->route('vendor.dashboard');
            }
        }
        
        return view('vendor.auth.login');
    }

    /**
     * Handle vendor login request (for both vendor owners and vendor staff)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user is a vendor owner
            if ($user->isVendor()) {
                $vendor = $user->vendor;
                
                if (!$vendor) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return back()->withErrors([
                        'email' => 'Vendor profile not found. Please contact support.',
                    ]);
                }
                
                if ($vendor->isPending()) {
                    return redirect()->route('vendor.pending');
                }
                
                if ($vendor->isRejected()) {
                    return redirect()->route('vendor.rejected');
                }
                
                if ($vendor->isSuspended()) {
                    return redirect()->route('vendor.suspended');
                }
                
                // Vendor is approved, redirect to dashboard
                return redirect()->intended(route('vendor.dashboard'));
            }
            
            // Check if user is a vendor staff
            if ($user->isVendorStaff()) {
                $staffRecord = $user->vendorStaff;
                
                if (!$staffRecord) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return back()->withErrors([
                        'email' => 'Staff profile not found. Please contact your vendor administrator.',
                    ]);
                }
                
                // Check if staff is active
                if (!$staffRecord->is_active) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return back()->withErrors([
                        'email' => 'Your staff account has been deactivated. Please contact your vendor administrator.',
                    ]);
                }
                
                $vendor = $staffRecord->vendor;
                
                if (!$vendor) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return back()->withErrors([
                        'email' => 'Associated vendor not found. Please contact support.',
                    ]);
                }
                
                if ($vendor->isPending()) {
                    return redirect()->route('vendor.pending');
                }
                
                if ($vendor->isRejected()) {
                    return redirect()->route('vendor.rejected');
                }
                
                if ($vendor->isSuspended()) {
                    return redirect()->route('vendor.suspended');
                }
                
                // Vendor is approved, redirect to dashboard
                return redirect()->intended(route('vendor.dashboard'));
            }
            
            // User is neither vendor nor vendor staff
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return back()->withErrors([
                'email' => 'You do not have vendor access. Please register as a vendor or contact your vendor administrator.',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Log the vendor out
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }

    /**
     * Show the vendor staff login form
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showStaffLoginForm()
    {
        // If user is already authenticated as vendor or vendor staff, redirect to dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isVendor() || $user->isVendorStaff()) {
                return redirect()->route('vendor.dashboard');
            }
        }
        
        return view('vendor.auth.staff-login');
    }

    /**
     * Handle vendor staff login request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function staffLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Ensure user is a vendor staff
            if (!$user->isVendorStaff()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'This login is for vendor staff only. If you are a vendor owner, please use the vendor login.',
                ])->withInput($request->only('email', 'remember'));
            }
            
            $staffRecord = $user->vendorStaff;
            
            if (!$staffRecord) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Staff profile not found. Please contact your vendor administrator.',
                ])->withInput($request->only('email', 'remember'));
            }
            
            // Check if staff is active
            if (!$staffRecord->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Your staff account has been deactivated. Please contact your vendor administrator.',
                ])->withInput($request->only('email', 'remember'));
            }
            
            $vendor = $staffRecord->vendor;
            
            if (!$vendor) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Associated vendor not found. Please contact support.',
                ])->withInput($request->only('email', 'remember'));
            }
            
            if ($vendor->isPending()) {
                return redirect()->route('vendor.pending');
            }
            
            if ($vendor->isRejected()) {
                return redirect()->route('vendor.rejected');
            }
            
            if ($vendor->isSuspended()) {
                return redirect()->route('vendor.suspended');
            }
            
            // Vendor is approved, redirect to dashboard
            return redirect()->intended(route('vendor.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }
}
