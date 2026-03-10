<?php

namespace App\Console\Commands;

use App\Jobs\ProcessBulkPayouts;
use App\Models\PayoutSchedule;
use App\Models\VendorWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:process-scheduled {--force : Force run regardless of schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled vendor payouts based on configured schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Starting scheduled payout processing...');
            Log::info('Starting scheduled payout processing');

            // Get the active payout schedule
            $schedule = PayoutSchedule::where('enabled', true)->first();
            
            if (!$schedule) {
                $this->warn('No active payout schedule found');
                return 0;
            }
            
            // Check if the schedule should run now, unless force flag is used
            $force = $this->option('force');
            if (!$force && !$schedule->shouldRunNow()) {
                $this->info('No payouts scheduled to run at this time. Use --force to run anyway.');
                return 0;
            }
            
            if ($force) {
                $this->info('Force flag detected - running payouts regardless of schedule');
            } else {
                if ($schedule->scheduled_at) {
                    $this->info('Processing payouts scheduled for ' . $schedule->scheduled_at->format('M d, Y h:i A'));
                } else {
                    $this->info('Processing payouts with ' . $schedule->frequency . ' schedule');
                }
            }
            
            // Get vendors with pending amount
            $eligibleWallets = VendorWallet::where('status', 'active')
                ->where('pending_amount', '>', 0)
                ->get();
                
            if ($eligibleWallets->isEmpty()) {
                $this->info('No eligible vendors found for payout');
                return 0;
            }
            
            $vendorIds = $eligibleWallets->pluck('vendor_id')->toArray();
            $totalAmount = $eligibleWallets->sum('pending_amount');
            
            $this->info('Found ' . count($vendorIds) . ' eligible vendors with total payout amount: ₹' . number_format($totalAmount, 2));
            
            // Dispatch bulk payout job
            // Get the first admin user ID, or use null if none exists
            $adminUser = \App\Models\User::whereIn('user_role', ['admin', 'super_admin'])->first();
            $adminUserId = $adminUser ? $adminUser->id : null;
            
            if (!$adminUserId) {
                $this->warn('No admin user found. Payout will be processed without a user reference.');
                Log::warning('Scheduled payout processing without admin user reference');
            }
            
            ProcessBulkPayouts::dispatch(
                $vendorIds,
                $schedule->payout_mode,
                'Automated scheduled payout - ' . ($schedule->scheduled_at ? $schedule->scheduled_at->format('M d, Y h:i A') : $schedule->frequency),
                true, // Pay full amount
                $adminUserId // System user ID
            );
            
            // Mark schedule as executed
            $schedule->markAsExecuted();
            
            $this->info('Bulk payout job dispatched successfully');
            Log::info('Scheduled payout job dispatched for ' . count($vendorIds) . ' vendors with total amount ₹' . $totalAmount);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error processing scheduled payouts: ' . $e->getMessage());
            Log::error('Error processing scheduled payouts: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
