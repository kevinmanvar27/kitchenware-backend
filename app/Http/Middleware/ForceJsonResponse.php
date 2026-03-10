<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * Forces the request to expect JSON responses by setting the Accept header.
     * This ensures that validation errors and exceptions return JSON instead of HTML.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force the request to accept JSON
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
