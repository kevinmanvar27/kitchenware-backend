<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

class NotificationController extends Controller
{
    use LogsActivity;

    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Delete a specific notification.
     */
    public function destroy($id)
    {
        $vendor = $this->getVendor();
        
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            $notificationType = $notification->type ?? 'notification';
            $notification->delete();
            
            // Log activity
            if ($vendor) {
                $this->logVendorActivity($vendor->id, 'dismissed', "Dismissed notification: {$notificationType}");
            }
        }

        // Get updated unread count
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Delete all notifications for the user.
     */
    public function destroyAll()
    {
        $vendor = $this->getVendor();
        
        $count = Notification::where('user_id', Auth::id())->count();
        Notification::where('user_id', Auth::id())->delete();
        
        // Log activity
        if ($vendor && $count > 0) {
            $this->logVendorActivity($vendor->id, 'dismissed', "Dismissed all {$count} notifications");
        }

        return response()->json([
            'success' => true,
            'unread_count' => 0
        ]);
    }

    /**
     * Mark a specific notification as read (kept for backward compatibility).
     */
    public function markAsRead($id)
    {
        $vendor = $this->getVendor();
        
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->update(['read' => true]);
            
            // Log activity
            if ($vendor) {
                $notificationType = $notification->type ?? 'notification';
                $this->logVendorActivity($vendor->id, 'updated', "Marked notification as read: {$notificationType}");
            }
        }

        // Get updated unread count
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark all notifications as read (kept for backward compatibility).
     */
    public function markAllAsRead()
    {
        $vendor = $this->getVendor();
        
        $count = Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->count();
            
        Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);
        
        // Log activity
        if ($vendor && $count > 0) {
            $this->logVendorActivity($vendor->id, 'updated', "Marked all {$count} notifications as read");
        }

        return response()->json([
            'success' => true,
            'unread_count' => 0
        ]);
    }
}
