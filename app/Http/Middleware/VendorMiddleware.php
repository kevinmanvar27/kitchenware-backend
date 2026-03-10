<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VendorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('vendor.login');
        }

        $user = Auth::user();

        // Check if user is a vendor owner
        if ($user->isVendor()) {
            $vendor = $user->vendor;
            
            if (!$vendor) {
                Auth::logout();
                return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
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
            
            return $next($request);
        }
        
        // Check if user is a vendor staff
        if ($user->isVendorStaff()) {
            $staffRecord = $user->vendorStaff;
            
            if (!$staffRecord) {
                Auth::logout();
                return redirect()->route('vendor.login')->with('error', 'Staff profile not found.');
            }
            
            // Check if staff is active
            if (!$staffRecord->is_active) {
                Auth::logout();
                return redirect()->route('vendor.login')->with('error', 'Your staff account has been deactivated.');
            }
            
            $vendor = $staffRecord->vendor;
            
            if (!$vendor) {
                Auth::logout();
                return redirect()->route('vendor.login')->with('error', 'Associated vendor not found.');
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
            
            return $next($request);
        }

        // User has no vendor access
        Auth::logout();
        return redirect()->route('vendor.login')->with('error', 'You do not have vendor access.');
    }
}
