<?php

namespace App\Providers;

use App\Services\MockRazorpayXService;
use App\Services\RazorpayXService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the RazorpayXService to use MockRazorpayXService in local environment
        if ($this->app->environment('local')) {
            $this->app->bind(RazorpayXService::class, function ($app) {
                return new MockRazorpayXService();
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}