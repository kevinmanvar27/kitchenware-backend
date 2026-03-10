<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiController;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API Endpoints for Notifications"
 * )
 */
class NotificationController extends ApiController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications list
     * 
     * @OA\Get(
     *      path="/api/v1/notifications",
     *      operationId="getUserNotifications",
     *      tags={"Notifications"},
     *      summary="Get user notifications",
     *      description="Get list of notifications for the authenticated user",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Items per page (default: 15, max: 50)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="unread_only",
     *          description="Filter to show only unread notifications",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="boolean")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = min($request->per_page ?? 15, 50);
        $unreadOnly = filter_var($request->unread_only, FILTER_VALIDATE_BOOLEAN);

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->where('read', false);
        }

        $notifications = $query->paginate($perPage);

        // Get unread count
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('read', false)
            ->count();

        return $this->sendResponse([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ], 'Notifications retrieved successfully.');
    }

    /**
     * Mark notification as read
     * 
     * @OA\Post(
     *      path="/api/v1/notifications/{id}/mark-read",
     *      operationId="markNotificationAsRead",
     *      tags={"Notifications"},
     *      summary="Mark notification as read",
     *      description="Mark a specific notification as read",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Notification id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Notification marked as read",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Notification not found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return $this->sendError('Notification not found.', [], 404);
        }

        if (!$notification->read) {
            $notification->read = true;
            $notification->save();
        }

        return $this->sendResponse($notification, 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read
     * 
     * @OA\Post(
     *      path="/api/v1/notifications/mark-all-read",
     *      operationId="markAllNotificationsAsRead",
     *      tags={"Notifications"},
     *      summary="Mark all notifications as read",
     *      description="Mark all notifications for the authenticated user as read",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="All notifications marked as read",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $updated = Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update(['read' => true]);

        return $this->sendResponse([
            'marked_count' => $updated,
        ], 'All notifications marked as read.');
    }

    /**
     * Get unread notifications count
     * 
     * @OA\Get(
     *      path="/api/v1/notifications/unread-count",
     *      operationId="getUnreadNotificationsCount",
     *      tags={"Notifications"},
     *      summary="Get unread notifications count",
     *      description="Get the count of unread notifications for the authenticated user",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = Notification::where('user_id', $user->id)
            ->where('read', false)
            ->count();

        return $this->sendResponse([
            'unread_count' => $count,
        ], 'Unread count retrieved successfully.');
    }

    /**
     * Delete a notification
     * 
     * @OA\Delete(
     *      path="/api/v1/notifications/{id}",
     *      operationId="deleteNotification",
     *      tags={"Notifications"},
     *      summary="Delete a notification",
     *      description="Delete a specific notification",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Notification id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Notification deleted",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Notification not found"
     *      )
     * )
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return $this->sendError('Notification not found.', [], 404);
        }

        $notification->delete();

        return $this->sendResponse(null, 'Notification deleted successfully.');
    }

    /**
     * Send notification to a single user
     *
     * @OA\Post(
     *      path="/api/v1/notifications/send-to-user",
     *      operationId="sendNotificationToUser",
     *      tags={"Notifications"},
     *      summary="Send notification to user",
     *      description="Send a push notification to a specific user (Admin only)",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_id", "title", "body"},
     *              @OA\Property(property="user_id", type="integer", example=1),
     *              @OA\Property(property="title", type="string", example="New Offer!"),
     *              @OA\Property(property="body", type="string", example="Check out our latest deals"),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Notification sent",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
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
            'data' => 'nullable|array'
        ]);

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

        // Send push notification via Firebase
        $payload = [
            'notification' => [
                'title' => $request->title,
                'body' => $request->body
            ],
            'data' => $request->data ?? []
        ];

        $result = $this->notificationService->sendToUser($user, $payload);

        return $this->sendResponse([
            'notification' => $notification,
            'push_result' => $result,
        ], 'Notification sent successfully.');
    }

    /**
     * Send notification to a user group
     *
     * @OA\Post(
     *      path="/api/v1/notifications/send-to-group",
     *      operationId="sendNotificationToUserGroup",
     *      tags={"Notifications"},
     *      summary="Send notification to user group",
     *      description="Send a push notification to all users in a group (Admin only)",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_group_id", "title", "body"},
     *              @OA\Property(property="user_group_id", type="integer", example=1),
     *              @OA\Property(property="title", type="string", example="Group Announcement"),
     *              @OA\Property(property="body", type="string", example="Important update for your group"),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Notification sent",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
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
            'data' => 'nullable|array'
        ]);

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

        // Send push notification via Firebase
        $payload = [
            'notification' => [
                'title' => $request->title,
                'body' => $request->body
            ],
            'data' => $request->data ?? []
        ];

        $result = $this->notificationService->sendToUserGroup($userGroup, $payload);

        return $this->sendResponse([
            'notifications_created' => count($notifications),
            'push_result' => $result,
        ], 'Notification sent to group successfully.');
    }

    /**
     * Register or update device token for a user
     *
     * @OA\Post(
     *      path="/api/v1/notifications/register-device",
     *      operationId="registerDeviceToken",
     *      tags={"Notifications"},
     *      summary="Register device token",
     *      description="Register or update FCM device token for push notifications",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"device_token"},
     *              @OA\Property(property="device_token", type="string", example="fcm_token_here")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Device token registered",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string'
        ]);

        $user = $request->user();
        $deviceToken = $request->device_token;
        
        // Validate the device token format
        if (!$this->notificationService->isValidDeviceToken($deviceToken)) {
            return $this->sendError('Invalid device token format.', [], 422);
        }
        
        // Use the User model method to update the token
        $user->updateDeviceToken($deviceToken);
        
        return $this->sendResponse(null, 'Device token registered successfully.');
    }

    /**
     * Get Firebase notification statistics
     *
     * @OA\Get(
     *      path="/api/v1/notifications/statistics",
     *      operationId="getNotificationStatistics",
     *      tags={"Notifications"},
     *      summary="Get notification statistics",
     *      description="Get Firebase notification statistics (Admin only)",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        $stats = $this->notificationService->getStatistics();
        return $this->sendResponse($stats, 'Statistics retrieved successfully.');
    }

    /**
     * Send notification to all users
     *
     * @OA\Post(
     *      path="/api/v1/notifications/send-to-all",
     *      operationId="sendNotificationToAllUsers",
     *      tags={"Notifications"},
     *      summary="Send notification to all users",
     *      description="Send a push notification to all users (Admin only)",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"title", "body"},
     *              @OA\Property(property="title", type="string", example="System Announcement"),
     *              @OA\Property(property="body", type="string", example="Important system update information"),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="exclude_admins", type="boolean", example=false),
     *              @OA\Property(property="only_with_device_token", type="boolean", example=true)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Notification sent",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToAllUsers(Request $request)
    {
        // Verify the user is an admin
        if (!$request->user()->hasAnyRole(['super_admin', 'admin'])) {
            return $this->sendError('Unauthorized. Only admins can send notifications to all users.', [], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
            'exclude_admins' => 'nullable|boolean',
            'only_with_device_token' => 'nullable|boolean'
        ]);

        // Build the payload
        $payload = [
            'title' => $request->title,
            'body' => $request->body,
            'message' => $request->body,
            'type' => 'broadcast',
            'data' => $request->data ?? []
        ];

        // Get options
        $excludeAdmins = $request->input('exclude_admins', false);
        $onlyWithDeviceToken = $request->input('only_with_device_token', true);

        // Send to all users
        $result = $this->notificationService->sendToAllUsers(
            $payload, 
            $excludeAdmins, 
            $onlyWithDeviceToken
        );

        return $this->sendResponse($result, 'Broadcast notification sent.');
    }

    /**
     * Get notification templates
     *
     * @OA\Get(
     *      path="/api/v1/notifications/templates",
     *      operationId="getNotificationTemplates",
     *      tags={"Notifications"},
     *      summary="Get notification templates",
     *      description="Get predefined notification templates (Admin only)",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplates(Request $request)
    {
        // Verify the user is an admin
        if (!$request->user()->hasAnyRole(['super_admin', 'admin'])) {
            return $this->sendError('Unauthorized. Only admins can access notification templates.', [], 403);
        }

        // Predefined templates
        $templates = [
            [
                'id' => 'welcome',
                'name' => 'Welcome Message',
                'title' => 'Welcome to our platform!',
                'body' => 'Thank you for joining our community. We\'re excited to have you on board.',
                'type' => 'welcome'
            ],
            [
                'id' => 'order_confirmation',
                'name' => 'Order Confirmation',
                'title' => 'Order Confirmed',
                'body' => 'Your order #ORDER_ID has been confirmed and is being processed.',
                'type' => 'order'
            ],
            [
                'id' => 'order_shipped',
                'name' => 'Order Shipped',
                'title' => 'Order Shipped',
                'body' => 'Your order #ORDER_ID has been shipped and is on its way to you!',
                'type' => 'order'
            ],
            [
                'id' => 'order_delivered',
                'name' => 'Order Delivered',
                'title' => 'Order Delivered',
                'body' => 'Your order #ORDER_ID has been delivered. Enjoy your purchase!',
                'type' => 'order'
            ],
            [
                'id' => 'low_stock',
                'name' => 'Low Stock Alert',
                'title' => '⚠️ Low Stock Alert',
                'body' => 'Product "PRODUCT_NAME" has low stock! Current quantity: QUANTITY, Threshold: THRESHOLD',
                'type' => 'low_stock_alert'
            ],
            [
                'id' => 'price_drop',
                'name' => 'Price Drop',
                'title' => 'Price Drop Alert!',
                'body' => 'Good news! Products you\'ve been interested in have dropped in price.',
                'type' => 'promotion'
            ],
            [
                'id' => 'new_promotion',
                'name' => 'New Promotion',
                'title' => 'New Promotion Available',
                'body' => 'Check out our latest promotion: PROMOTION_NAME. Limited time offer!',
                'type' => 'promotion'
            ],
            [
                'id' => 'system_maintenance',
                'name' => 'System Maintenance',
                'title' => 'Scheduled Maintenance',
                'body' => 'Our system will be undergoing maintenance on MAINTENANCE_DATE. Service may be temporarily unavailable.',
                'type' => 'system'
            ]
        ];

        return $this->sendResponse($templates, 'Notification templates retrieved successfully.');
    }

    /**
     * Get notification counts by type
     *
     * @OA\Get(
     *      path="/api/v1/notifications/counts-by-type",
     *      operationId="getNotificationCountsByType",
     *      tags={"Notifications"},
     *      summary="Get notification counts by type",
     *      description="Get counts of notifications grouped by type for the authenticated user",
     *      security={{"sanctum": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountsByType(Request $request)
    {
        $user = $request->user();

        // Get counts by type
        $counts = Notification::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as total, SUM(CASE WHEN read = 0 THEN 1 ELSE 0 END) as unread')
            ->groupBy('type')
            ->get()
            ->map(function($item) {
                return [
                    'type' => $item->type,
                    'total' => (int)$item->total,
                    'unread' => (int)$item->unread
                ];
            });

        // Add total count
        $totalCount = Notification::where('user_id', $user->id)->count();
        $totalUnread = Notification::where('user_id', $user->id)->where('read', false)->count();

        return $this->sendResponse([
            'by_type' => $counts,
            'total' => [
                'total' => $totalCount,
                'unread' => $totalUnread
            ]
        ], 'Notification counts retrieved successfully.');
    }

    /**
     * Mark all notifications of a specific type as read
     * 
     * @OA\Post(
     *      path="/api/v1/notifications/mark-type-read",
     *      operationId="markNotificationTypeAsRead",
     *      tags={"Notifications"},
     *      summary="Mark all notifications of a type as read",
     *      description="Mark all notifications of a specific type as read for the authenticated user",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"type"},
     *              @OA\Property(property="type", type="string", example="order")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Notifications marked as read",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markTypeAsRead(Request $request)
    {
        $request->validate([
            'type' => 'required|string'
        ]);

        $user = $request->user();
        $type = $request->type;

        $updated = Notification::where('user_id', $user->id)
            ->where('type', $type)
            ->where('read', false)
            ->update(['read' => true]);

        return $this->sendResponse([
            'marked_count' => $updated,
            'type' => $type
        ], "All notifications of type '{$type}' marked as read.");
    }
}