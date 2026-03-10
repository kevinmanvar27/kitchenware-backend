<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VendorFeatureSetting;

class CheckVendorPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions  The required permissions (any one of them is sufficient)
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // If user is not authenticated, redirect to vendor login
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('vendor.login');
        }

        $user = Auth::user();
        
        // Get the vendor ID for feature checking
        $vendorId = null;
        if ($user->isVendor()) {
            $vendorId = $user->vendor?->id;
        } elseif ($user->isVendorStaff()) {
            $vendorId = $user->vendorStaff?->vendor_id;
        }
        
        // Check if the feature is enabled for this vendor (if vendor ID is available)
        if ($vendorId && !empty($permissions)) {
            // The permission key often maps to a feature key
            // Check if any of the required permissions/features is enabled
            $featureEnabled = false;
            foreach ($permissions as $permission) {
                if (VendorFeatureSetting::isFeatureEnabledForVendor($vendorId, $permission)) {
                    $featureEnabled = true;
                    break;
                }
            }
            
            if (!$featureEnabled) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'This feature is not available for your store.'], 403);
                }
                return redirect()->route('vendor.dashboard')->with('error', 'This feature is not available for your store.');
            }
        }
        
        // Vendor owners have all permissions (after feature check)
        if ($user->isVendor()) {
            return $next($request);
        }
        
        // Check if vendor staff has any of the required permissions
        if ($user->isVendorStaff()) {
            $staffRecord = $user->vendorStaff;
            
            if (!$staffRecord || !$staffRecord->is_active) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Your staff account is not active.'], 403);
                }
                Auth::logout();
                return redirect()->route('vendor.login')->with('error', 'Your staff account is not active.');
            }
            
            // Check if staff has any of the required permissions
            if ($staffRecord->hasAnyPermission($permissions)) {
                return $next($request);
            }
            
            // No permission
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            
            return redirect()->route('vendor.dashboard')->with('error', 'You do not have permission to access this section.');
        }
        
        // Not a vendor or vendor staff
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Access denied.'], 403);
        }
        
        return redirect()->route('vendor.login')->with('error', 'Access denied.');
    }
}
