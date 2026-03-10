<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class SetTheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Share theme settings with all views
        $setting = Setting::first();
        if ($setting) {
            view()->share('font_color', $setting->font_color ?? '#333333');
            view()->share('font_style', $setting->font_style ?? 'Figtree, sans-serif');
            view()->share('theme_color', $setting->theme_color ?? '#FF6B00');
            view()->share('background_color', $setting->background_color ?? '#FFFFFF');
            view()->share('sidebar_text_color', $setting->sidebar_text_color ?? '#333333');
            view()->share('heading_text_color', $setting->heading_text_color ?? '#333333');
            view()->share('label_text_color', $setting->label_text_color ?? '#333333');
            view()->share('general_text_color', $setting->general_text_color ?? '#333333');
        }
        
        return $next($request);
    }
}