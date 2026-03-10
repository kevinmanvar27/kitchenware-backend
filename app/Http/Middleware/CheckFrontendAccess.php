<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class CheckFrontendAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the frontend access permission setting
        $setting = Setting::first();
        $accessPermission = $setting->frontend_access_permission ?? 'open_for_all';
        
        // For "Open for all", allow access to everyone without any restrictions
        if ($accessPermission === 'open_for_all') {
            return $next($request);
        }
        
        // Handle different access permission scenarios
        switch ($accessPermission) {
            case 'registered_users_only':
                // Only allow access to authenticated users
                if (!Auth::check()) {
                    // Allow access to cart page for guests to view their cart
                    if ($request->is('cart*')) {
                        return $next($request);
                    }
                    return redirect()->route('login');
                }
                break;
                
            case 'admin_approval_required':
                // Check if user is authenticated
                if (!Auth::check()) {
                    // For admin approval required, unauthenticated users can access login/register pages
                    // We need to check if the current route is a protected route
                    $protectedRoutes = ['frontend.home'];
                    if (in_array($request->route()->getName(), $protectedRoutes)) {
                        return redirect()->route('login');
                    }
                    // Allow access to cart page for guests to view their cart
                    if ($request->is('cart*')) {
                        return $next($request);
                    }
                } else {
                    // Check if user is approved
                    $user = Auth::user();
                    if (!$user->is_approved) {
                        // Show pending approval message
                        return response()->view('errors.pending-approval', [
                            'message' => $setting->pending_approval_message ?? 'Your account is pending approval. Please wait for admin approval before accessing the site.'
                        ], 403);
                    }
                }
                break;
        }
        
        return $next($request);
    }
}