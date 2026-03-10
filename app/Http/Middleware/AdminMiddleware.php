<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if the authenticated user is a User model (not VendorCustomer)
        if (!($user instanceof \App\Models\User)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Access denied. Admin panel is not accessible with this account type.'], 403);
            }
            
            return redirect()->route('login')->withErrors([
                'email' => 'Access denied. Admin panel is not accessible with this account type.',
            ]);
        }

        // Check if user has an allowed role for admin panel
        if (!in_array($user->user_role, $this->allowedRoles)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Access denied. You do not have permission to access the admin panel.'], 403);
            }
            
            return redirect()->route('login')->withErrors([
                'email' => 'Access denied. You do not have permission to access the admin panel.',
            ]);
        }

        // Check if user is approved (for non-super_admin users)
        if ($user->user_role !== 'super_admin' && !$user->is_approved) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Your account is pending approval.'], 403);
            }
            
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is pending approval. Please contact an administrator.',
            ]);
        }

        return $next($request);
    }
}
