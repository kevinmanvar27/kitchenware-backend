<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Models\Vendor;
use App\Models\SubscriptionReminder;
use App\Notifications\SubscriptionExpiring;
use App\Notifications\SubscriptionExpired;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiry
                            {--force : Force send reminders even if already sent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring and expired subscriptions and send reminders to vendors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting subscription expiry check...');
        
        $force = $this->option('force');
        
        // Check for subscriptions expiring in 7 days
        $this->checkExpiringSubscriptions(7, SubscriptionReminder::TYPE_7_DAYS, $force);
        
        // Check for subscriptions expiring in 3 days
        $this->checkExpiringSubscriptions(3, SubscriptionReminder::TYPE_3_DAYS, $force);
        
        // Check for subscriptions expiring in 1 day
        $this->checkExpiringSubscriptions(1, SubscriptionReminder::TYPE_1_DAY, $force);
        
        // Check for expired subscriptions
        $this->checkExpiredSubscriptions($force);
        
        $this->info('Subscription expiry check completed!');
        
        return Command::SUCCESS;
    }

    /**
     * Check for subscriptions expiring in X days and send reminders.
     */
    protected function checkExpiringSubscriptions(int $days, string $reminderType, bool $force = false)
    {
        $this->info("Checking for subscriptions expiring in {$days} day(s)...");
        
        // Get the target date (X days from now)
        $targetDate = Carbon::now()->addDays($days)->startOfDay();
        $endOfTargetDate = $targetDate->copy()->endOfDay();
        
        // Find active subscriptions expiring on the target date
        $subscriptions = UserSubscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$targetDate, $endOfTargetDate])
            ->with(['user', 'vendor', 'plan'])
            ->get();
        
        $count = 0;
        
        foreach ($subscriptions as $subscription) {
            // Skip if vendor or user doesn't exist
            if (!$subscription->vendor || !$subscription->user) {
                continue;
            }
            
            // Check if reminder already sent (unless forced)
            if (!$force) {
                $existingReminder = SubscriptionReminder::where('subscription_id', $subscription->id)
                    ->where('reminder_type', $reminderType)
                    ->where('status', SubscriptionReminder::STATUS_SENT)
                    ->exists();
                
                if ($existingReminder) {
                    $this->line("  Skipping {$subscription->vendor->store_name} - reminder already sent");
                    continue;
                }
            }
            
            try {
                // Send notification
                $subscription->user->notify(new SubscriptionExpiring(
                    $subscription->vendor,
                    $subscription,
                    $days
                ));
                
                // Create or update reminder record
                $reminder = SubscriptionReminder::updateOrCreate(
                    [
                        'subscription_id' => $subscription->id,
                        'reminder_type' => $reminderType,
                    ],
                    [
                        'vendor_id' => $subscription->vendor_id,
                        'user_id' => $subscription->user_id,
                        'status' => SubscriptionReminder::STATUS_SENT,
                        'sent_at' => now(),
                        'error_message' => null,
                    ]
                );
                
                $this->line("  ✓ Sent {$days}-day reminder to {$subscription->vendor->store_name} ({$subscription->user->email})");
                $count++;
                
                Log::info("Subscription expiry reminder sent", [
                    'subscription_id' => $subscription->id,
                    'vendor_id' => $subscription->vendor_id,
                    'user_email' => $subscription->user->email,
                    'reminder_type' => $reminderType,
                    'days_remaining' => $days,
                ]);
                
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send reminder to {$subscription->vendor->store_name}: {$e->getMessage()}");
                
                // Log the failure
                SubscriptionReminder::updateOrCreate(
                    [
                        'subscription_id' => $subscription->id,
                        'reminder_type' => $reminderType,
                    ],
                    [
                        'vendor_id' => $subscription->vendor_id,
                        'user_id' => $subscription->user_id,
                        'status' => SubscriptionReminder::STATUS_FAILED,
                        'error_message' => $e->getMessage(),
                    ]
                );
                
                Log::error("Failed to send subscription expiry reminder", [
                    'subscription_id' => $subscription->id,
                    'vendor_id' => $subscription->vendor_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("  Sent {$count} reminder(s) for subscriptions expiring in {$days} day(s)");
    }

    /**
     * Check for expired subscriptions and send notifications.
     */
    protected function checkExpiredSubscriptions(bool $force = false)
    {
        $this->info("Checking for expired subscriptions...");
        
        // Find subscriptions that expired today
        $today = Carbon::now()->startOfDay();
        $endOfToday = $today->copy()->endOfDay();
        
        $subscriptions = UserSubscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [$today, $endOfToday])
            ->with(['user', 'vendor', 'plan'])
            ->get();
        
        $count = 0;
        
        foreach ($subscriptions as $subscription) {
            // Skip if vendor or user doesn't exist
            if (!$subscription->vendor || !$subscription->user) {
                continue;
            }
            
            // Check if reminder already sent (unless forced)
            if (!$force) {
                $existingReminder = SubscriptionReminder::where('subscription_id', $subscription->id)
                    ->where('reminder_type', SubscriptionReminder::TYPE_EXPIRED)
                    ->where('status', SubscriptionReminder::STATUS_SENT)
                    ->exists();
                
                if ($existingReminder) {
                    $this->line("  Skipping {$subscription->vendor->store_name} - expiry notification already sent");
                    continue;
                }
            }
            
            try {
                // Update subscription status to expired
                $subscription->update(['status' => 'expired']);
                
                // Send notification
                $subscription->user->notify(new SubscriptionExpired(
                    $subscription->vendor,
                    $subscription
                ));
                
                // Create reminder record
                SubscriptionReminder::updateOrCreate(
                    [
                        'subscription_id' => $subscription->id,
                        'reminder_type' => SubscriptionReminder::TYPE_EXPIRED,
                    ],
                    [
                        'vendor_id' => $subscription->vendor_id,
                        'user_id' => $subscription->user_id,
                        'status' => SubscriptionReminder::STATUS_SENT,
                        'sent_at' => now(),
                        'error_message' => null,
                    ]
                );
                
                $this->line("  ✓ Sent expiry notification to {$subscription->vendor->store_name} ({$subscription->user->email})");
                $count++;
                
                Log::info("Subscription expired notification sent", [
                    'subscription_id' => $subscription->id,
                    'vendor_id' => $subscription->vendor_id,
                    'user_email' => $subscription->user->email,
                ]);
                
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send expiry notification to {$subscription->vendor->store_name}: {$e->getMessage()}");
                
                // Log the failure
                SubscriptionReminder::updateOrCreate(
                    [
                        'subscription_id' => $subscription->id,
                        'reminder_type' => SubscriptionReminder::TYPE_EXPIRED,
                    ],
                    [
                        'vendor_id' => $subscription->vendor_id,
                        'user_id' => $subscription->user_id,
                        'status' => SubscriptionReminder::STATUS_FAILED,
                        'error_message' => $e->getMessage(),
                    ]
                );
                
                Log::error("Failed to send subscription expired notification", [
                    'subscription_id' => $subscription->id,
                    'vendor_id' => $subscription->vendor_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("  Sent {$count} expiry notification(s)");
    }
}
