<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVendorOwner
{
    /**
     * Handle an incoming request.
     * Only allow vendor owners, not staff members.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not authenticated, redirect to login
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user is a vendor owner (not staff)
        if ($user->isVendor()) {
            return $next($request);
        }
        
        // Not a vendor owner
        if ($request->expectsJson()) {
            return response()->json(['error' => 'This feature is only available to store owners.'], 403);
        }
        
        return redirect()->route('vendor.dashboard')->with('error', 'This feature is only available to store owners.');
    }
}
