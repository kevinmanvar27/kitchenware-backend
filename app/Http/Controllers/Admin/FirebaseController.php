<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Notification;
use App\Models\VendorCustomer;
use App\Models\ScheduledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FirebaseController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Test Firebase configuration
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConfiguration()
    {
        $result = $this->notificationService->testConfiguration();
        return response()->json($result);
    }

    /**
     * Get Firebase notification statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        $stats = $this->notificationService->getStatistics();
        return response()->json($stats);
    }

    /**
     * Show the notification sending form
     *
     * @return \Illuminate\View\View
     */
    public function showNotificationForm()
    {
        $users = User::all();
        $userGroups = UserGroup::with('users')->get();
        
        // Get counts for display
        $usersWithTokens = User::whereNotNull('device_token')->where('device_token', '!=', '')->count();
        $vendorCustomersWithTokens = VendorCustomer::whereNotNull('device_token')->where('device_token', '!=', '')->count();
        
        // Note: Scheduled notifications are now loaded via DataTables AJAX
        return view('admin.notifications.send', compact('users', 'userGroups', 'usersWithTokens', 'vendorCustomersWithTokens'));
    }

    /**
     * Send notification to a single user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
            'schedule_type' => 'nullable|in:immediate,scheduled',
            'scheduled_at' => 'required_if:schedule_type,scheduled|nullable|date|after:now',
        ]);

        // Check if this is a scheduled notification
        if ($request->schedule_type === 'scheduled' && $request->scheduled_at) {
            return $this->scheduleAdminNotification($request, 'user');
        }

        $user = User::findOrFail($request->user_id);

        // Store notification in database
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'message' => $request->body,
            'type' => 'push',
            'data' => $request->data ?? [],
            'read' => false,
        ]);

        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'message' => $request->body,
            'type' => 'push',
            'data' => $request->data ?? []
        ];

        // Notification is already saved to database (for bell icon)
        // Now attempt to send push notification if user has device token
        $result = $this->notificationService->sendToUser($user, $payload, false);
        
        // Add notification info to result
        $result['notification_id'] = $notification->id;
        $result['saved_to_database'] = true;
        
        // If no device token, still return success since notification was saved
        if (!$result['success'] && empty($user->device_token)) {
            $result['success'] = true;
            $result['message'] = 'Notification saved to database (user has no device token for push notification)';
            $result['push_sent'] = false;
        } else {
            $result['push_sent'] = !empty($user->device_token);
        }

        return response()->json($result);
    }

    /**
     * Send notification to a user group
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToUserGroup(Request $request)
    {
        $request->validate([
            'user_group_id' => 'required|exists:user_groups,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
            'schedule_type' => 'nullable|in:immediate,scheduled',
            'scheduled_at' => 'required_if:schedule_type,scheduled|nullable|date|after:now',
        ]);

        // Check if this is a scheduled notification
        if ($request->schedule_type === 'scheduled' && $request->scheduled_at) {
            return $this->scheduleAdminNotification($request, 'group');
        }

        $userGroup = UserGroup::with('users')->findOrFail($request->user_group_id);

        // Store notifications in database for each user in the group
        $notifications = [];
        foreach ($userGroup->users as $user) {
            $notifications[] = Notification::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'message' => $request->body,
                'type' => 'push',
                'data' => $request->data ?? [],
                'read' => false,
            ]);
        }

        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'message' => $request->body,
            'type' => 'push',
            'data' => $request->data ?? []
        ];

        // Notifications are already saved to database (for bell icon)
        // Now attempt to send push notifications to users with device tokens
        $result = $this->notificationService->sendToUserGroupWithoutSave($userGroup, $payload);
        
        // Add notifications count to result
        $result['notifications_created'] = count($notifications);
        $result['saved_to_database'] = count($notifications);
        
        // Update success status - notifications saved is a success even if push fails
        if (count($notifications) > 0) {
            $result['success'] = true;
            $pushSuccessful = $result['summary']['successful'] ?? 0;
            $pushFailed = $result['summary']['failed'] ?? 0;
            $result['message'] = "Notifications saved for " . count($notifications) . " users (bell icon). Push notifications: {$pushSuccessful} successful, {$pushFailed} failed";
        }

        return response()->json($result);
    }
    
    /**
     * Send notification to all users and vendor customers
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToAllUsers(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
            'exclude_admins' => 'nullable|boolean',
            'include_vendor_customers' => 'nullable|boolean',
            'schedule_type' => 'nullable|in:immediate,scheduled',
            'scheduled_at' => 'required_if:schedule_type,scheduled|nullable|date|after:now',
        ]);

        // Check if this is a scheduled notification
        if ($request->schedule_type === 'scheduled' && $request->scheduled_at) {
            return $this->scheduleAdminNotification($request, 'all_users');
        }

        // Build the payload
        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'message' => $request->body,
            'type' => 'broadcast',
            'data' => $request->data ?? []
        ];

        // Get options
        $excludeAdmins = filter_var($request->input('exclude_admins', false), FILTER_VALIDATE_BOOLEAN);
        $includeVendorCustomers = filter_var($request->input('include_vendor_customers', true), FILTER_VALIDATE_BOOLEAN);

        // Send to all users - notifications are always saved to database for bell icon
        // Push notifications are only sent to users with device tokens
        $userResult = $this->notificationService->sendToAllUsers(
            $payload, 
            $excludeAdmins
        );

        $vendorCustomerResult = [
            'success' => false,
            'summary' => [
                'total_customers' => 0,
                'successful' => 0,
                'failed' => 0
            ]
        ];

        // Also send to vendor customers if requested
        if ($includeVendorCustomers) {
            $vendorCustomerResult = $this->sendToAllVendorCustomers($payload);
        }

        // Combine results
        $totalUsers = ($userResult['summary']['total_users'] ?? 0) + ($vendorCustomerResult['summary']['total_customers'] ?? 0);
        $totalSaved = ($userResult['summary']['saved_to_database'] ?? $userResult['summary']['total_users'] ?? 0);
        $totalPushSuccessful = ($userResult['summary']['push_successful'] ?? $userResult['summary']['successful'] ?? 0) + ($vendorCustomerResult['summary']['successful'] ?? 0);
        $totalPushFailed = ($userResult['summary']['push_failed'] ?? $userResult['summary']['failed'] ?? 0) + ($vendorCustomerResult['summary']['failed'] ?? 0);

        return response()->json([
            'success' => $totalSaved > 0,
            'message' => "Notifications saved for {$totalSaved} users (bell icon). Push notifications: {$totalPushSuccessful} successful, {$totalPushFailed} failed",
            'summary' => [
                'total_users' => $totalUsers,
                'saved_to_database' => $totalSaved,
                'successful' => $totalPushSuccessful,
                'failed' => $totalPushFailed,
                'users_breakdown' => [
                    'app_users' => $userResult['summary'] ?? [],
                    'vendor_customers' => $vendorCustomerResult['summary'] ?? []
                ]
            ]
        ]);
    }
    
    /**
     * Schedule an admin notification for later
     *
     * @param Request $request
     * @param string $targetType
     * @return \Illuminate\Http\JsonResponse
     */
    protected function scheduleAdminNotification(Request $request, string $targetType)
    {
        $scheduledNotification = ScheduledNotification::create([
            'vendor_id' => null,
            'is_admin_notification' => true,
            'title' => $request->title,
            'body' => $request->body,
            'data' => $request->data,
            'target_type' => $targetType,
            'user_id' => $targetType === 'user' ? $request->user_id : null,
            'user_group_id' => $targetType === 'group' ? $request->user_group_id : null,
            'scheduled_at' => Carbon::parse($request->scheduled_at),
            'status' => ScheduledNotification::STATUS_PENDING,
            'created_by' => Auth::id(),
        ]);

        Log::info('Admin scheduled notification created', [
            'notification_id' => $scheduledNotification->id,
            'target_type' => $targetType,
            'scheduled_at' => $scheduledNotification->scheduled_at,
        ]);

        $scheduledTime = Carbon::parse($request->scheduled_at)->format('M d, Y h:i A');
        $message = "Notification scheduled successfully for {$scheduledTime}";

        return response()->json([
            'success' => true,
            'message' => $message,
            'notification' => $scheduledNotification,
            'scheduled' => true
        ]);
    }
    
    /**
     * Get a scheduled notification for editing or viewing
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getScheduledNotification($id)
    {
        $notification = ScheduledNotification::admin()->find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        return response()->json([
            'success' => true,
            'notification' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data ? json_encode($notification->data) : '',
                'target_type' => $notification->target_type,
                'user_id' => $notification->user_id,
                'user_group_id' => $notification->user_group_id,
                'scheduled_at' => $notification->scheduled_at->format('Y-m-d\TH:i'),
                'sent_at' => $notification->sent_at ? $notification->sent_at->format('Y-m-d\TH:i') : null,
                'status' => $notification->status,
                'success_count' => $notification->success_count,
                'fail_count' => $notification->fail_count,
                'error_message' => $notification->error_message,
                'is_editable' => $notification->isEditable(),
            ]
        ]);
    }
    
    /**
     * Update a scheduled notification
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateScheduledNotification(Request $request, $id)
    {
        $notification = ScheduledNotification::admin()->find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if (!$notification->isEditable()) {
            return response()->json(['success' => false, 'message' => 'This notification cannot be edited. Only pending notifications can be modified.'], 400);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable|json',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $notification->update([
            'title' => $request->title,
            'body' => $request->body,
            'data' => $request->data ? json_decode($request->data, true) : null,
            'scheduled_at' => Carbon::parse($request->scheduled_at),
        ]);

        Log::info('Admin scheduled notification updated', [
            'notification_id' => $notification->id,
            'scheduled_at' => $notification->scheduled_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification updated successfully',
            'notification' => $notification
        ]);
    }
    
    /**
     * Cancel a scheduled notification
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelScheduledNotification($id)
    {
        $notification = ScheduledNotification::admin()->find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if (!$notification->isCancellable()) {
            return response()->json(['success' => false, 'message' => 'This notification cannot be cancelled.'], 400);
        }

        $notification->update([
            'status' => ScheduledNotification::STATUS_CANCELLED,
        ]);

        Log::info('Admin scheduled notification cancelled', [
            'notification_id' => $notification->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification cancelled successfully'
        ]);
    }

    /**
     * Get notifications data for DataTable (server-side processing)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationsData(Request $request)
    {
        $query = ScheduledNotification::admin()
            ->with(['user', 'userGroup']);

        // Handle search
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('title', 'like', "%{$searchValue}%")
                  ->orWhere('body', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%")
                  ->orWhere('target_type', 'like', "%{$searchValue}%");
            });
        }

        // Handle status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Handle target type filter
        if ($request->has('target_type') && !empty($request->target_type)) {
            $query->where('target_type', $request->target_type);
        }

        // Get total count before filtering
        $totalRecords = ScheduledNotification::admin()->count();
        
        // Get filtered count
        $filteredRecords = $query->count();

        // Handle ordering
        $orderColumnIndex = $request->input('order.0.column', 2); // Default to scheduled_at column
        $orderDirection = $request->input('order.0.dir', 'desc');
        
        $columns = ['title', 'target_type', 'scheduled_at', 'status', 'success_count', 'id'];
        $orderColumn = $columns[$orderColumnIndex] ?? 'scheduled_at';
        
        $query->orderBy($orderColumn, $orderDirection);

        // Handle pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $notifications = $query->skip($start)->take($length)->get();

        // Format data for DataTable
        $data = $notifications->map(function ($notification) {
            // Format target
            $target = '';
            if ($notification->target_type === 'user') {
                $userName = $notification->user ? $notification->user->name : 'User #' . $notification->user_id;
                $target = '<span class="badge bg-info rounded-pill"><i class="fas fa-user me-1"></i>' . e($userName) . '</span>';
            } elseif ($notification->target_type === 'group') {
                $groupName = $notification->userGroup ? $notification->userGroup->name : 'Group #' . $notification->user_group_id;
                $target = '<span class="badge bg-primary rounded-pill"><i class="fas fa-users me-1"></i>' . e($groupName) . '</span>';
            } elseif ($notification->target_type === 'all_users') {
                $target = '<span class="badge bg-danger rounded-pill"><i class="fas fa-globe me-1"></i>All Users</span>';
            } else {
                $target = '<span class="badge bg-secondary rounded-pill">' . e($notification->target_type) . '</span>';
            }

            // Format status
            $statusBadge = match($notification->status) {
                'pending' => '<span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-clock me-1"></i>Pending</span>',
                'sent' => '<span class="badge bg-success rounded-pill"><i class="fas fa-check-circle me-1"></i>Sent</span>',
                'failed' => '<span class="badge bg-danger rounded-pill" data-bs-toggle="tooltip" title="' . e($notification->error_message) . '"><i class="fas fa-times-circle me-1"></i>Failed</span>',
                'cancelled' => '<span class="badge bg-secondary rounded-pill"><i class="fas fa-ban me-1"></i>Cancelled</span>',
                default => '<span class="badge bg-light text-dark rounded-pill">' . e($notification->status) . '</span>',
            };

            // Format results
            $results = '-';
            if ($notification->status === 'sent') {
                $results = '<span class="text-success"><i class="fas fa-check me-1"></i>' . ($notification->success_count ?? 0) . '</span>';
                if ($notification->fail_count > 0) {
                    $results .= '<span class="text-danger ms-2"><i class="fas fa-times me-1"></i>' . $notification->fail_count . '</span>';
                }
            }

            // Format actions
            $actions = '';
            if ($notification->status === 'pending') {
                $actions = '
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1" onclick="editNotification(' . $notification->id . ')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" onclick="cancelNotification(' . $notification->id . ')" title="Cancel">
                        <i class="fas fa-times"></i>
                    </button>';
            } else {
                $actions = '
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="viewNotificationDetails(' . $notification->id . ')" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>';
            }

            return [
                'title' => '<div class="fw-medium">' . e(\Str::limit($notification->title, 30)) . '</div><small class="text-muted">' . e(\Str::limit($notification->body, 40)) . '</small>',
                'target' => $target,
                'scheduled_at' => '<div>' . $notification->scheduled_at->format('M d, Y') . '</div><small class="text-muted">' . $notification->scheduled_at->format('h:i A') . '</small>',
                'status' => $statusBadge,
                'results' => $results,
                'actions' => '<div class="text-end">' . $actions . '</div>',
                'scheduled_at_raw' => $notification->scheduled_at->timestamp,
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Send notification to all vendor customers
     *
     * @param array $payload
     * @return array
     */
    private function sendToAllVendorCustomers(array $payload): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;
        $totalCustomers = 0;

        // Get all active vendor customers with device tokens
        $customers = VendorCustomer::where('is_active', true)
            ->whereNotNull('device_token')
            ->where('device_token', '!=', '')
            ->get();

        foreach ($customers as $customer) {
            $totalCustomers++;
            
            // Send push notification
            $result = $this->notificationService->sendPushNotification($customer->device_token, $payload);
            $result['customer_id'] = $customer->id;
            $result['customer_name'] = $customer->name;
            $results[] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        Log::info('Admin broadcast notification sent to vendor customers', [
            'total_customers' => $totalCustomers,
            'successful' => $successCount,
            'failed' => $failCount
        ]);

        return [
            'success' => $successCount > 0,
            'message' => "Notifications sent to vendor customers: {$successCount} successful, {$failCount} failed out of {$totalCustomers}",
            'results' => $results,
            'summary' => [
                'total_customers' => $totalCustomers,
                'successful' => $successCount,
                'failed' => $failCount
            ]
        ];
    }
    
    /**
     * Send a test notification to a specific device token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testNotification(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'message' => $request->body,
            'type' => 'test',
            'data' => $request->data ?? []
        ];

        $result = $this->notificationService->sendPushNotification($request->device_token, $payload);

        return response()->json($result);
    }
}