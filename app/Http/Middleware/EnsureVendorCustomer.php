<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VendorCustomer;

class EnsureVendorCustomer
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that the authenticated user is a VendorCustomer
     * and that their account is active.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Check if the authenticated user is a VendorCustomer
        if (!$user instanceof VendorCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer access required.',
                'data' => null
            ], 403);
        }

        // Check if the customer account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact the vendor.',
                'data' => null
            ], 403);
        }

        // Check if the vendor is still approved
        $vendor = $user->vendor;
        if (!$vendor || !$vendor->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'The vendor store is not available.',
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}
