<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Admin allowed roles that can access the admin panel.
     * 
     * @var array
     */
    protected $allowedRoles = [
        'super_admin',
        'admin',
        'editor',
        'staff',
    ];

    /**
     * Show the login form
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle login request
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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if the authenticated user is a User model (not VendorCustomer or other)
            if (!($user instanceof User)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Access denied. Admin panel is not accessible with this account type.',
                ]);
            }
            
            // Check if user has an allowed role for admin panel
            // Blocked roles: 'user', 'vendor', or any other non-admin role
            if (!in_array($user->user_role, $this->allowedRoles)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                $message = 'You do not have access to the admin area.';
                
                if ($user->user_role === 'vendor') {
                    $message = 'Vendors should use the vendor portal to login.';
                } elseif ($user->user_role === 'user') {
                    $message = 'Regular users do not have access to the admin area.';
                }
                
                return back()->withErrors([
                    'email' => $message,
                ]);
            }
            
            // Check if user is approved (for non-super_admin users)
            if ($user->user_role !== 'super_admin' && !$user->is_approved) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Your account is pending approval. Please contact an administrator.',
                ]);
            }
            
            // For all allowed roles, redirect to admin dashboard
            return redirect()->intended('admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}