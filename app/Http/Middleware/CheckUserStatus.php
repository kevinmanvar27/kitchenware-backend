<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is suspended or blocked
            if (in_array($user->status, ['Suspend', 'Block'])) {
                // Log out the user
                Auth::logout();
                
                // Invalidate the session
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect with error message
                $message = $user->status === 'Block' 
                    ? 'Your account has been blocked. Please contact support for assistance.'
                    : 'Your account has been suspended. Please contact support for assistance.';
                
                return redirect()->route('login')->with('error', $message);
            }
            
            // Check if user is pending or under review (optional - you can allow them to browse but restrict certain actions)
            if (in_array($user->status, ['Pending', 'Under review'])) {
                // You can add a flash message to inform them
                if (!$request->session()->has('status_warning_shown')) {
                    $message = $user->status === 'Pending' 
                        ? 'Your account is pending approval. Some features may be restricted.'
                        : 'Your account is under review. Some features may be restricted.';
                    
                    session()->flash('warning', $message);
                    $request->session()->put('status_warning_shown', true);
                }
            }
        }
        
        return $next($request);
    }
}
