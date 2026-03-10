<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, return null to trigger a 401 JSON response
        // instead of redirecting to a login page
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // For vendor routes, redirect to vendor login
        if ($request->is('vendor/*') || $request->routeIs('vendor.*')) {
            return route('vendor.login');
        }

        return route('login');
    }
}
