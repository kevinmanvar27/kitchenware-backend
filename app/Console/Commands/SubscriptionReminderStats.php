<?php

namespace App\Console\Commands;

use App\Models\SubscriptionReminder;
use App\Models\UserSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SubscriptionReminderStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:reminder-stats
                            {--days=30 : Number of days to look back}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display statistics about subscription reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Subscription Reminder Statistics (Last {$days} days)");
        $this->line(str_repeat('=', 60));
        $this->newLine();
        
        // Total reminders sent
        $totalSent = SubscriptionReminder::where('status', SubscriptionReminder::STATUS_SENT)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
        
        $this->info("📧 Total Reminders Sent: {$totalSent}");
        $this->newLine();
        
        // Reminders by type
        $this->line("Reminders by Type:");
        $this->line(str_repeat('-', 60));
        
        $byType = SubscriptionReminder::select('reminder_type', DB::raw('count(*) as total'))
            ->where('status', SubscriptionReminder::STATUS_SENT)
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('reminder_type')
            ->get();
        
        $typeLabels = [
            SubscriptionReminder::TYPE_7_DAYS => '7 Days Before Expiry',
            SubscriptionReminder::TYPE_3_DAYS => '3 Days Before Expiry',
            SubscriptionReminder::TYPE_1_DAY => '1 Day Before Expiry',
            SubscriptionReminder::TYPE_EXPIRED => 'Subscription Expired',
        ];
        
        foreach ($byType as $stat) {
            $label = $typeLabels[$stat->reminder_type] ?? $stat->reminder_type;
            $this->line("  {$label}: {$stat->total}");
        }
        
        $this->newLine();
        
        // Failed reminders
        $totalFailed = SubscriptionReminder::where('status', SubscriptionReminder::STATUS_FAILED)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
        
        if ($totalFailed > 0) {
            $this->error("❌ Failed Reminders: {$totalFailed}");
            
            $failed = SubscriptionReminder::where('status', SubscriptionReminder::STATUS_FAILED)
                ->where('created_at', '>=', now()->subDays($days))
                ->with(['vendor', 'user'])
                ->latest()
                ->limit(5)
                ->get();
            
            if ($failed->count() > 0) {
                $this->newLine();
                $this->line("Recent Failed Reminders:");
                $this->line(str_repeat('-', 60));
                
                foreach ($failed as $reminder) {
                    $this->line("  • {$reminder->vendor->store_name} ({$reminder->user->email})");
                    $this->line("    Error: {$reminder->error_message}");
                    $this->line("    Date: {$reminder->created_at->format('M d, Y H:i')}");
                    $this->newLine();
                }
            }
        } else {
            $this->info("✓ No Failed Reminders");
        }
        
        $this->newLine();
        
        // Upcoming subscriptions to expire
        $this->line("Upcoming Expirations:");
        $this->line(str_repeat('-', 60));
        
        $upcomingCounts = [
            '7 days' => UserSubscription::where('status', 'active')
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now()->addDays(7)->startOfDay(), now()->addDays(7)->endOfDay()])
                ->count(),
            '3 days' => UserSubscription::where('status', 'active')
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now()->addDays(3)->startOfDay(), now()->addDays(3)->endOfDay()])
                ->count(),
            '1 day' => UserSubscription::where('status', 'active')
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now()->addDays(1)->startOfDay(), now()->addDays(1)->endOfDay()])
                ->count(),
            'today' => UserSubscription::where('status', 'active')
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
        ];
        
        foreach ($upcomingCounts as $period => $count) {
            $icon = $count > 0 ? '⚠️' : '✓';
            $this->line("  {$icon} Expiring in {$period}: {$count}");
        }
        
        $this->newLine();
        
        // Active subscriptions
        $activeCount = UserSubscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now())
            ->count();
        
        $this->info("📊 Total Active Subscriptions: {$activeCount}");
        
        // Expired subscriptions
        $expiredCount = UserSubscription::where('status', 'expired')
            ->orWhere(function($query) {
                $query->where('status', 'active')
                      ->whereNotNull('ends_at')
                      ->where('ends_at', '<', now());
            })
            ->count();
        
        $this->line("⏰ Total Expired Subscriptions: {$expiredCount}");
        
        $this->newLine();
        $this->line(str_repeat('=', 60));
        
        return Command::SUCCESS;
    }
}
