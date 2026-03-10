<?php

namespace App\Console\Commands;

use App\Models\PayoutSchedule;
use Illuminate\Console\Command;

class RunScheduledPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:run-scheduled {--force : Force run regardless of schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the scheduled payouts manually (for testing)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running scheduled payouts command...');
        
        // Call the ProcessScheduledPayouts command
        $force = $this->option('force');
        
        if ($force) {
            $this->info('Force flag detected - running payouts regardless of schedule');
            $this->call('payouts:process-scheduled', ['--force' => true]);
            return 0;
        }
        
        // Check if there's an active schedule
        $schedule = PayoutSchedule::where('enabled', true)->first();
        
        if (!$schedule) {
            $this->error('No active payout schedule found');
            return 1;
        }
        
        if ($schedule->scheduled_at) {
            $this->info('Found active schedule for: ' . $schedule->scheduled_at->format('M d, Y h:i A'));
        } else {
            $this->info('Found active schedule: ' . $schedule->frequency);
        }
        
        // Check if schedule should run now
        if (!$schedule->shouldRunNow()) {
            $this->warn('Schedule is not configured to run at this time. Use --force to run anyway.');
            return 1;
        }
        
        $this->info('Schedule is configured to run now. Processing...');
        $this->call('payouts:process-scheduled');
        
        return 0;
    }
}
