<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ProcessPaidInvoices::class,
        Commands\ProcessScheduledPayouts::class,
        Commands\RunScheduledPayouts::class,
        Commands\TestVendorPayout::class,
        Commands\SyncVendorEarnings::class,
        Commands\FixVendorCommission::class,
        Commands\ResetVendorEarnings::class,
        Commands\ProcessScheduledNotifications::class,
        Commands\CheckSubscriptionExpiry::class,
        Commands\SubscriptionReminderStats::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process paid invoices every 5 minutes to update vendor earnings quickly
        $schedule->command('invoices:process-paid')->everyFiveMinutes();
        
        // Sync vendor earnings every hour (catches any missed invoices)
        $schedule->command('vendors:sync-earnings')->hourly();
        
        // Run the command to check for scheduled payouts every 5 minutes
        $schedule->command('payouts:run-scheduled')->everyFiveMinutes();
        
        // Process scheduled push notifications every minute
        $schedule->command('notifications:process-scheduled')->everyMinute();
        
        // Check for expiring and expired subscriptions daily at 9:00 AM
        $schedule->command('subscriptions:check-expiry')->dailyAt('09:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}