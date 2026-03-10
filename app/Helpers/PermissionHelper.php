<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('hasPermission')) {
    /**
     * Check if the authenticated user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    function hasPermission($permission)
    {
        if (!Auth::check()) {
            return false;
        }
        
        /** @var User $user */
        $user = Auth::user();
        
        // Use method_exists for IDE compatibility
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }
        
        return false;
    }
}

if (!function_exists('hasRole')) {
    /**
     * Check if the authenticated user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    function hasRole($role)
    {
        if (!Auth::check()) {
            return false;
        }
        
        /** @var User $user */
        $user = Auth::user();
        
        // Use method_exists for IDE compatibility
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }
        
        return false;
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Check if the authenticated user is a super admin.
     *
     * @return bool
     */
    function isSuperAdmin()
    {
        if (!Auth::check()) {
            return false;
        }
        
        /** @var User $user */
        $user = Auth::user();
        
        // Use method_exists for IDE compatibility
        if (method_exists($user, 'isSuperAdmin')) {
            return $user->isSuperAdmin();
        }
        
        return false;
    }
}