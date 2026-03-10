<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // If user has 'user' role, redirect to frontend home
                if ($user->user_role === 'user') {
                    return redirect()->route('frontend.home');
                }
                
                // For all other roles (super_admin, admin, etc.), redirect to admin dashboard
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}