<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckVendorSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Check if user is authenticated and has vendor role
        if (!$user || !$user->hasRole('vendor')) {
            return redirect()->route('login');
        }
        
        // Get vendor
        $vendor = $user->vendor;
        
        if (!$vendor) {
            return redirect()->route('login')->with('error', 'Vendor profile not found.');
        }
        
        // Check if vendor is approved
        if (!$vendor->isApproved()) {
            return redirect()->route('vendor.pending');
        }
        
        // Allow access to subscription routes even without active subscription
        $allowedRoutes = [
            'vendor.subscription.plans',
            'vendor.subscription.subscribe',
            'vendor.subscription.payment',
            'vendor.subscription.verify',
            'vendor.subscription.current',
            'vendor.subscription.cancel',
            'vendor.logout',
        ];
        
        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }
        
        // Check if vendor has active subscription
        if (!$vendor->hasActiveSubscription()) {
            // Redirect to subscription plans page
            return redirect()->route('vendor.subscription.plans')
                ->with('warning', 'Please purchase a subscription plan to access the vendor panel.');
        }
        
        return $next($request);
    }
}
