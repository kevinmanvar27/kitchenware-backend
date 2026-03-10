<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorCustomer;
use App\Models\Vendor;
use App\Models\ScheduledNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PushNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display the push notifications page
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            abort(403, 'Vendor not found');
        }

        // Get all customers for this vendor
        $customers = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get customers with device tokens
        $customersWithTokens = $customers->filter(function ($customer) {
            return !empty($customer->device_token);
        });

        // Note: Scheduled notifications are now loaded via DataTables AJAX
        return view('vendor.push-notifications.index', compact('customers', 'customersWithTokens'));
    }

    /**
     * Send notification to all vendor's customers (immediate or scheduled)
     */
    public function sendToAll(Request $request)
    {
        Log::info('Vendor sendToAll request received', [
            'schedule_type' => $request->schedule_type,
            'scheduled_at' => $request->scheduled_at,
            'title' => $request->title,
            'has_body' => !empty($request->body),
        ]);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable', // Can be JSON string or null
            'schedule_type' => 'nullable|in:immediate,scheduled',
            'scheduled_at' => 'required_if:schedule_type,scheduled|nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            Log::warning('Vendor notification validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->except(['_token']),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vendor = $this->getVendor();
        
        if (!$vendor) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Vendor not found'], 403);
            }
            return redirect()->back()->with('error', 'Vendor not found');
        }

        // Check if this is a scheduled notification
        if ($request->schedule_type === 'scheduled' && $request->scheduled_at) {
            return $this->scheduleNotification($request, $vendor, 'all');
        }

        // Immediate send - existing logic
        $customers = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->whereNotNull('device_token')
            ->where('device_token', '!=', '')
            ->get();

        if ($customers->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No customers with registered devices found'], 400);
            }
            return redirect()->back()->with('error', 'No customers with registered devices found');
        }

        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'message' => $request->body,
            'type' => 'vendor_notification',
            'data' => array_merge(
                $request->data ? json_decode($request->data, true) : [],
                [
                    'type' => 'vendor_notification',
                    'vendor_id' => (string) $vendor->id,
                    'vendor_name' => $vendor->store_name ?? $vendor->user->name ?? 'Vendor'
                ]
            )
        ];

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($customers as $customer) {
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

        Log::info('Vendor push notification sent to all customers', [
            'vendor_id' => $vendor->id,
            'total_customers' => $customers->count(),
            'successful' => $successCount,
            'failed' => $failCount
        ]);

        $message = "Notifications sent: {$successCount} successful, {$failCount} failed out of {$customers->count()} customers";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $successCount > 0,
                'message' => $message,
                'summary' => [
                    'successful' => $successCount,
                    'failed' => $failCount,
                    'total' => $customers->count()
                ]
            ]);
        }

        if ($successCount > 0) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Send notification to specific customers (immediate or scheduled)
     */
    public function sendToCustomers(Request $request)
    {
        Log::info('Vendor sendToCustomers request received', [
            'schedule_type' => $request->schedule_type,
            'scheduled_at' => $request->scheduled_at,
            'customer_ids_count' => is_array($request->customer_ids) ? count($request->customer_ids) : 0,
            'title' => $request->title,
        ]);
        
        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'required|integer|exists:vendor_customers,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable', // Can be JSON string or null
            'schedule_type' => 'nullable|in:immediate,scheduled',
            'scheduled_at' => 'required_if:schedule_type,scheduled|nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            Log::warning('Vendor notification validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->except(['_token']),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vendor = $this->getVendor();
        
        if (!$vendor) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Vendor not found'], 403);
            }
            return redirect()->back()->with('error', 'Vendor not found');
        }

        // Check if this is a scheduled notification
        if ($request->schedule_type === 'scheduled' && $request->scheduled_at) {
            return $this->scheduleNotification($request, $vendor, 'selected', $request->customer_ids);
        }

        // Immediate send - existing logic
        $customers = VendorCustomer::where('vendor_id', $vendor->id)
            ->whereIn('id', $request->customer_ids)
            ->get();

        if ($customers->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No valid customers found'], 400);
            }
            return redirect()->back()->with('error', 'No valid customers found');
        }

        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'message' => $request->body,
            'type' => 'vendor_notification',
            'data' => array_merge(
                $request->data ? json_decode($request->data, true) : [],
                [
                    'type' => 'vendor_notification',
                    'vendor_id' => (string) $vendor->id,
                    'vendor_name' => $vendor->store_name ?? $vendor->user->name ?? 'Vendor'
                ]
            )
        ];

        $results = [];
        $successCount = 0;
        $failCount = 0;
        $noTokenCount = 0;

        foreach ($customers as $customer) {
            if (empty($customer->device_token)) {
                $results[] = [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'success' => false,
                    'message' => 'No device token registered'
                ];
                $noTokenCount++;
                continue;
            }

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

        Log::info('Vendor push notification sent to selected customers', [
            'vendor_id' => $vendor->id,
            'total_selected' => $customers->count(),
            'successful' => $successCount,
            'failed' => $failCount,
            'no_token' => $noTokenCount
        ]);

        $message = "Notifications sent: {$successCount} successful, {$failCount} failed";
        if ($noTokenCount > 0) {
            $message .= ", {$noTokenCount} customers without device tokens";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $successCount > 0,
                'message' => $message,
                'summary' => [
                    'successful' => $successCount,
                    'failed' => $failCount,
                    'no_device_token' => $noTokenCount
                ]
            ]);
        }

        if ($successCount > 0) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Schedule a notification for later
     */
    protected function scheduleNotification(Request $request, $vendor, $targetType, $customerIds = null)
    {
        try {
            // Handle data field - it might be a string (JSON) or already decoded
            $data = null;
            if ($request->data) {
                $data = is_string($request->data) ? json_decode($request->data, true) : $request->data;
            }
            
            Log::info('Creating vendor scheduled notification', [
                'vendor_id' => $vendor->id,
                'target_type' => $targetType,
                'customer_ids' => $customerIds,
                'scheduled_at' => $request->scheduled_at,
                'title' => $request->title,
            ]);
            
            $scheduledNotification = ScheduledNotification::create([
                'vendor_id' => $vendor->id,
                'is_admin_notification' => false, // Explicitly set for vendor notifications
                'title' => $request->title,
                'body' => $request->body,
                'data' => $data,
                'target_type' => $targetType,
                'customer_ids' => $customerIds,
                'scheduled_at' => Carbon::parse($request->scheduled_at),
                'status' => ScheduledNotification::STATUS_PENDING,
                'created_by' => Auth::id(),
            ]);

            Log::info('Vendor scheduled notification created', [
                'notification_id' => $scheduledNotification->id,
                'vendor_id' => $vendor->id,
                'scheduled_at' => $scheduledNotification->scheduled_at,
            ]);

            $scheduledTime = Carbon::parse($request->scheduled_at)->format('M d, Y h:i A');
            $message = "Notification scheduled successfully for {$scheduledTime}";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'notification' => $scheduledNotification
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to create vendor scheduled notification', [
                'vendor_id' => $vendor->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to schedule notification: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to schedule notification: ' . $e->getMessage());
        }
    }

    /**
     * Get a scheduled notification for editing
     */
    public function getScheduledNotification($id)
    {
        Log::info('getScheduledNotification called', ['id' => $id]);
        
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            Log::warning('getScheduledNotification: Vendor not found');
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 403);
        }
        
        Log::info('getScheduledNotification: Vendor found', ['vendor_id' => $vendor->id]);

        $notification = ScheduledNotification::where('vendor_id', $vendor->id)
            ->where('is_admin_notification', false)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            Log::warning('getScheduledNotification: Notification not found', [
                'id' => $id,
                'vendor_id' => $vendor->id
            ]);
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        
        Log::info('getScheduledNotification: Notification found', ['notification_id' => $notification->id]);

        return response()->json([
            'success' => true,
            'notification' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data ? json_encode($notification->data) : '',
                'target_type' => $notification->target_type,
                'customer_ids' => $notification->customer_ids,
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
     */
    public function updateScheduledNotification(Request $request, $id)
    {
        Log::info('updateScheduledNotification called', [
            'id' => $id,
            'request_data' => $request->all()
        ]);
        
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            Log::warning('updateScheduledNotification: Vendor not found');
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 403);
        }
        
        Log::info('updateScheduledNotification: Vendor found', ['vendor_id' => $vendor->id]);

        $notification = ScheduledNotification::where('vendor_id', $vendor->id)
            ->where('is_admin_notification', false)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            Log::warning('updateScheduledNotification: Notification not found', [
                'id' => $id,
                'vendor_id' => $vendor->id
            ]);
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if (!$notification->isEditable()) {
            Log::warning('updateScheduledNotification: Notification not editable', [
                'id' => $id,
                'status' => $notification->status
            ]);
            return response()->json(['success' => false, 'message' => 'This notification cannot be edited. Only pending notifications can be modified.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'data' => 'nullable', // Can be JSON string or null
            'scheduled_at' => 'required|date|after:now',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'integer|exists:vendor_customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle data field - it might be a string (JSON) or already decoded
        $data = null;
        if ($request->data) {
            $data = is_string($request->data) ? json_decode($request->data, true) : $request->data;
        }
        
        $notification->update([
            'title' => $request->title,
            'body' => $request->body,
            'data' => $data,
            'scheduled_at' => Carbon::parse($request->scheduled_at),
            'customer_ids' => $request->customer_ids,
        ]);

        Log::info('Scheduled notification updated', [
            'notification_id' => $notification->id,
            'vendor_id' => $vendor->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification updated successfully',
            'notification' => $notification
        ]);
    }

    /**
     * Cancel a scheduled notification
     */
    public function cancelScheduledNotification($id)
    {
        Log::info('cancelScheduledNotification called', ['id' => $id]);
        
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            Log::warning('cancelScheduledNotification: Vendor not found');
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 403);
        }
        
        Log::info('cancelScheduledNotification: Vendor found', ['vendor_id' => $vendor->id]);

        $notification = ScheduledNotification::where('vendor_id', $vendor->id)
            ->where('is_admin_notification', false)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            Log::warning('cancelScheduledNotification: Notification not found', [
                'id' => $id,
                'vendor_id' => $vendor->id
            ]);
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        if (!$notification->isCancellable()) {
            Log::warning('cancelScheduledNotification: Notification not cancellable', [
                'id' => $id,
                'status' => $notification->status
            ]);
            return response()->json(['success' => false, 'message' => 'This notification cannot be cancelled.'], 400);
        }

        $notification->update([
            'status' => ScheduledNotification::STATUS_CANCELLED,
        ]);

        Log::info('Scheduled notification cancelled', [
            'notification_id' => $notification->id,
            'vendor_id' => $vendor->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification cancelled successfully'
        ]);
    }

    /**
     * Delete a scheduled notification
     */
    public function deleteScheduledNotification($id)
    {
        Log::info('deleteScheduledNotification called', ['id' => $id]);
        
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            Log::warning('deleteScheduledNotification: Vendor not found');
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 403);
        }
        
        Log::info('deleteScheduledNotification: Vendor found', ['vendor_id' => $vendor->id]);

        $notification = ScheduledNotification::where('vendor_id', $vendor->id)
            ->where('is_admin_notification', false)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            Log::warning('deleteScheduledNotification: Notification not found', [
                'id' => $id,
                'vendor_id' => $vendor->id
            ]);
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        
        Log::info('deleteScheduledNotification: Deleting notification', ['notification_id' => $notification->id]);

        $notification->delete();

        Log::info('Scheduled notification deleted', [
            'notification_id' => $id,
            'vendor_id' => $vendor->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Get list of customers for AJAX dropdown
     */
    public function getCustomers(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found'
            ], 403);
        }

        $query = VendorCustomer::where('vendor_id', $vendor->id)
            ->where('is_active', true);

        // Optional: filter by search term
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'mobile_number' => $customer->mobile_number,
                'has_device_token' => !empty($customer->device_token)
            ];
        });

        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    /**
     * Check Firebase configuration status
     */
    public function checkFirebaseStatus()
    {
        $result = $this->notificationService->testConfiguration();
        
        return response()->json($result);
    }

    /**
     * Get notifications data for DataTable (server-side processing)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotificationsData(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $query = ScheduledNotification::where('vendor_id', $vendor->id)
            ->where('is_admin_notification', false);

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
        $totalRecords = ScheduledNotification::where('vendor_id', $vendor->id)
            ->where('is_admin_notification', false)
            ->count();
        
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
            if ($notification->target_type === 'all') {
                $target = '<span class="badge bg-info rounded-pill"><i class="fas fa-users me-1"></i>All Customers</span>';
            } elseif ($notification->target_type === 'selected') {
                $customerCount = count($notification->customer_ids ?? []);
                $target = '<span class="badge bg-secondary rounded-pill"><i class="fas fa-user-check me-1"></i>' . $customerCount . ' Selected</span>';
            } else {
                $target = '<span class="badge bg-secondary rounded-pill">' . e($notification->target_type) . '</span>';
            }

            // Format status
            $statusHtml = '<span class="badge ' . $notification->getStatusBadgeClass() . ' status-badge rounded-pill">';
            if ($notification->status === 'pending') {
                $statusHtml .= '<i class="fas fa-clock me-1"></i>';
            } elseif ($notification->status === 'sent') {
                $statusHtml .= '<i class="fas fa-check me-1"></i>';
            } elseif ($notification->status === 'failed') {
                $statusHtml .= '<i class="fas fa-times me-1"></i>';
            } else {
                $statusHtml .= '<i class="fas fa-ban me-1"></i>';
            }
            $statusHtml .= $notification->getStatusLabel() . '</span>';

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
            if ($notification->isEditable()) {
                $actions .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1" onclick="editNotification(' . $notification->id . ')" title="Edit"><i class="fas fa-edit"></i></button>';
                $actions .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-pill me-1" onclick="cancelNotification(' . $notification->id . ')" title="Cancel"><i class="fas fa-ban"></i></button>';
            }
            $actions .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteNotification(' . $notification->id . ')" title="Delete"><i class="fas fa-trash"></i></button>';

            return [
                'title' => '<div class="fw-medium text-truncate" style="max-width: 200px;" title="' . e($notification->title) . '">' . e($notification->title) . '</div><small class="text-muted text-truncate d-block" style="max-width: 200px;" title="' . e($notification->body) . '">' . e(\Str::limit($notification->body, 50)) . '</small>',
                'target' => $target,
                'scheduled_at' => '<div class="d-flex flex-column"><span>' . $notification->scheduled_at->format('M d, Y') . '</span><small class="text-muted">' . $notification->scheduled_at->format('h:i A') . '</small></div>',
                'status' => $statusHtml,
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
}
