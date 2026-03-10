<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledNotification;
use App\Models\VendorCustomer;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ProcessScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send scheduled push notifications';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled notifications...');

        // Get all due notifications (both vendor and admin)
        $notifications = ScheduledNotification::due()
            ->with(['vendor', 'user', 'userGroup'])
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('No scheduled notifications to process.');
        } else {
            $this->info("Found {$notifications->count()} notification(s) to process.");

            foreach ($notifications as $notification) {
                if ($notification->is_admin_notification) {
                    $this->processAdminNotification($notification);
                } else {
                    $this->processVendorNotification($notification);
                }
            }

            $this->info('Scheduled notifications processing completed.');
        }

        // Cleanup old sent notifications (older than 48 hours)
        $this->cleanupOldNotifications();

        return 0;
    }

    /**
     * Remove sent/failed/cancelled notifications older than 48 hours
     */
    protected function cleanupOldNotifications()
    {
        $this->info('Cleaning up old notifications...');

        $cutoffTime = now()->subHours(48);

        $deleted = ScheduledNotification::whereIn('status', [
                ScheduledNotification::STATUS_SENT,
                ScheduledNotification::STATUS_FAILED,
                ScheduledNotification::STATUS_CANCELLED,
            ])
            ->where('sent_at', '<', $cutoffTime)
            ->delete();

        if ($deleted > 0) {
            $this->info("Deleted {$deleted} old notification(s) older than 48 hours.");
            Log::info('Old scheduled notifications cleaned up', ['deleted_count' => $deleted]);
        } else {
            $this->info('No old notifications to clean up.');
        }
    }

    /**
     * Process a single vendor scheduled notification
     */
    protected function processVendorNotification(ScheduledNotification $notification)
    {
        $this->info("Processing VENDOR notification ID: {$notification->id} for vendor: {$notification->vendor_id}");

        try {
            // Get customers based on target type
            if ($notification->target_type === ScheduledNotification::TARGET_ALL) {
                $customers = VendorCustomer::where('vendor_id', $notification->vendor_id)
                    ->where('is_active', true)
                    ->whereNotNull('device_token')
                    ->where('device_token', '!=', '')
                    ->get();
            } else {
                $customers = VendorCustomer::where('vendor_id', $notification->vendor_id)
                    ->whereIn('id', $notification->customer_ids ?? [])
                    ->get();
            }

            if ($customers->isEmpty()) {
                $notification->update([
                    'status' => ScheduledNotification::STATUS_FAILED,
                    'error_message' => 'No customers with registered devices found',
                    'sent_at' => now(),
                ]);
                $this->warn("No customers found for notification ID: {$notification->id}");
                return;
            }

            // Prepare payload
            $payload = [
                'title' => $notification->title,
                'body' => $notification->body,
                'message' => $notification->body,
                'type' => 'vendor_notification',
                'data' => array_merge(
                    $notification->data ?? [],
                    [
                        'type' => 'vendor_notification',
                        'vendor_id' => (string) $notification->vendor_id,
                        'vendor_name' => $notification->vendor->store_name ?? $notification->vendor->user->name ?? 'Vendor',
                        'scheduled_notification_id' => (string) $notification->id,
                    ]
                )
            ];

            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($customers as $customer) {
                if (empty($customer->device_token)) {
                    $failCount++;
                    continue;
                }

                $result = $this->notificationService->sendPushNotification($customer->device_token, $payload);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Customer {$customer->id}: " . ($result['message'] ?? 'Unknown error');
                }
            }

            // Update notification status
            $status = $successCount > 0 ? ScheduledNotification::STATUS_SENT : ScheduledNotification::STATUS_FAILED;
            
            $notification->update([
                'status' => $status,
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'error_message' => !empty($errors) ? implode('; ', array_slice($errors, 0, 5)) : null,
                'sent_at' => now(),
            ]);

            Log::info('Scheduled vendor notification processed', [
                'notification_id' => $notification->id,
                'vendor_id' => $notification->vendor_id,
                'status' => $status,
                'success_count' => $successCount,
                'fail_count' => $failCount,
            ]);

            $this->info("Vendor Notification ID: {$notification->id} - Sent: {$successCount}, Failed: {$failCount}");

        } catch (\Exception $e) {
            Log::error('Failed to process scheduled vendor notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            $notification->update([
                'status' => ScheduledNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            $this->error("Failed to process vendor notification ID: {$notification->id} - {$e->getMessage()}");
        }
    }

    /**
     * Process a single admin scheduled notification
     */
    protected function processAdminNotification(ScheduledNotification $notification)
    {
        $this->info("Processing ADMIN notification ID: {$notification->id}, target_type: {$notification->target_type}");

        try {
            $successCount = 0;
            $failCount = 0;
            $savedCount = 0;
            $errors = [];

            // Prepare payload
            $payload = [
                'title' => $notification->title,
                'body' => $notification->body,
                'message' => $notification->body,
                'type' => 'admin_notification',
                'data' => array_merge(
                    $notification->data ?? [],
                    [
                        'type' => 'admin_notification',
                        'scheduled_notification_id' => (string) $notification->id,
                    ]
                )
            ];

            switch ($notification->target_type) {
                case ScheduledNotification::TARGET_USER:
                    // Send to a single user
                    $result = $this->sendToSingleUser($notification, $payload);
                    $successCount = $result['success_count'];
                    $failCount = $result['fail_count'];
                    $savedCount = $result['saved_count'];
                    $errors = $result['errors'];
                    break;

                case ScheduledNotification::TARGET_GROUP:
                    // Send to all users in a group
                    $result = $this->sendToUserGroup($notification, $payload);
                    $successCount = $result['success_count'];
                    $failCount = $result['fail_count'];
                    $savedCount = $result['saved_count'];
                    $errors = $result['errors'];
                    break;

                case ScheduledNotification::TARGET_ALL_USERS:
                    // Send to all users
                    $result = $this->sendToAllUsers($notification, $payload);
                    $successCount = $result['success_count'];
                    $failCount = $result['fail_count'];
                    $savedCount = $result['saved_count'];
                    $errors = $result['errors'];
                    break;

                default:
                    throw new \Exception("Unknown target type: {$notification->target_type}");
            }

            // Update notification status - success if at least one notification was saved
            $status = $savedCount > 0 ? ScheduledNotification::STATUS_SENT : ScheduledNotification::STATUS_FAILED;
            
            $notification->update([
                'status' => $status,
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'error_message' => !empty($errors) ? implode('; ', array_slice($errors, 0, 5)) : null,
                'sent_at' => now(),
            ]);

            Log::info('Scheduled admin notification processed', [
                'notification_id' => $notification->id,
                'target_type' => $notification->target_type,
                'status' => $status,
                'saved_count' => $savedCount,
                'success_count' => $successCount,
                'fail_count' => $failCount,
            ]);

            $this->info("Admin Notification ID: {$notification->id} - Saved: {$savedCount}, Push Sent: {$successCount}, Push Failed: {$failCount}");

        } catch (\Exception $e) {
            Log::error('Failed to process scheduled admin notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            $notification->update([
                'status' => ScheduledNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            $this->error("Failed to process admin notification ID: {$notification->id} - {$e->getMessage()}");
        }
    }

    /**
     * Send notification to a single user
     */
    protected function sendToSingleUser(ScheduledNotification $notification, array $payload): array
    {
        $successCount = 0;
        $failCount = 0;
        $savedCount = 0;
        $errors = [];

        $user = User::find($notification->user_id);

        if (!$user) {
            return [
                'success_count' => 0,
                'fail_count' => 1,
                'saved_count' => 0,
                'errors' => ['User not found: ' . $notification->user_id]
            ];
        }

        // Save notification to database (for bell icon)
        Notification::create([
            'user_id' => $user->id,
            'title' => $notification->title,
            'message' => $notification->body,
            'type' => 'push',
            'data' => $notification->data ?? [],
            'read' => false,
        ]);
        $savedCount++;

        // Send push notification if user has device token
        if (!empty($user->device_token)) {
            $result = $this->notificationService->sendPushNotification($user->device_token, $payload);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "User {$user->id}: " . ($result['message'] ?? 'Unknown error');
            }
        }

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'saved_count' => $savedCount,
            'errors' => $errors
        ];
    }

    /**
     * Send notification to all users in a group
     */
    protected function sendToUserGroup(ScheduledNotification $notification, array $payload): array
    {
        $successCount = 0;
        $failCount = 0;
        $savedCount = 0;
        $errors = [];

        $userGroup = UserGroup::with('users')->find($notification->user_group_id);

        if (!$userGroup) {
            return [
                'success_count' => 0,
                'fail_count' => 1,
                'saved_count' => 0,
                'errors' => ['User group not found: ' . $notification->user_group_id]
            ];
        }

        foreach ($userGroup->users as $user) {
            // Save notification to database (for bell icon)
            Notification::create([
                'user_id' => $user->id,
                'title' => $notification->title,
                'message' => $notification->body,
                'type' => 'push',
                'data' => $notification->data ?? [],
                'read' => false,
            ]);
            $savedCount++;

            // Send push notification if user has device token
            if (!empty($user->device_token)) {
                $result = $this->notificationService->sendPushNotification($user->device_token, $payload);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "User {$user->id}: " . ($result['message'] ?? 'Unknown error');
                }
            }
        }

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'saved_count' => $savedCount,
            'errors' => $errors
        ];
    }

    /**
     * Send notification to all users
     */
    protected function sendToAllUsers(ScheduledNotification $notification, array $payload): array
    {
        $successCount = 0;
        $failCount = 0;
        $savedCount = 0;
        $errors = [];

        // Get all users (chunked to avoid memory issues)
        User::chunk(100, function($users) use ($notification, $payload, &$successCount, &$failCount, &$savedCount, &$errors) {
            foreach ($users as $user) {
                // Save notification to database (for bell icon)
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $notification->title,
                    'message' => $notification->body,
                    'type' => 'push',
                    'data' => $notification->data ?? [],
                    'read' => false,
                ]);
                $savedCount++;

                // Send push notification if user has device token
                if (!empty($user->device_token)) {
                    $result = $this->notificationService->sendPushNotification($user->device_token, $payload);
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failCount++;
                        // Only store first few errors to avoid memory issues
                        if (count($errors) < 10) {
                            $errors[] = "User {$user->id}: " . ($result['message'] ?? 'Unknown error');
                        }
                    }
                }
            }
        });

        // Also send to vendor customers (same behavior as immediate send)
        $vendorCustomerResult = $this->sendToVendorCustomers($payload);
        $successCount += $vendorCustomerResult['success_count'];
        $failCount += $vendorCustomerResult['fail_count'];

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'saved_count' => $savedCount,
            'errors' => $errors
        ];
    }

    /**
     * Send notification to all vendor customers (for all_users target type)
     */
    protected function sendToVendorCustomers(array $payload): array
    {
        $successCount = 0;
        $failCount = 0;

        // Get all active vendor customers with device tokens
        $customers = VendorCustomer::where('is_active', true)
            ->whereNotNull('device_token')
            ->where('device_token', '!=', '')
            ->get();

        foreach ($customers as $customer) {
            $result = $this->notificationService->sendPushNotification($customer->device_token, $payload);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount
        ];
    }
}
