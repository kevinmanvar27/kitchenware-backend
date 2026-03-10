<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Define scheduled tasks for the application.
|
*/

// Process paid invoices every 5 minutes to update vendor earnings quickly
Schedule::command('invoices:process-paid')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->description('Process paid invoices to update vendor earnings');

// Sync vendor earnings every hour (catches any missed invoices)
Schedule::command('vendors:sync-earnings')
    ->hourly()
    ->withoutOverlapping()
    ->description('Sync vendor earnings hourly');

// Run the command to check for scheduled payouts every 5 minutes
Schedule::command('payouts:run-scheduled')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->description('Run scheduled vendor payouts');

// Process scheduled push notifications every minute
Schedule::command('notifications:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->description('Process and send scheduled push notifications');

// Process vendor payouts on the 1st of every month at 2:00 AM
Schedule::command('vendors:process-payouts --min-amount=100 --mode=NEFT')
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->emailOutputOnFailure(env('ADMIN_EMAIL'))
    ->description('Process monthly vendor payouts via RazorpayX');

// Check for expiring and expired subscriptions daily at 9:00 AM
Schedule::command('subscriptions:check-expiry')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->description('Check for expiring subscriptions and send reminders to vendors');
