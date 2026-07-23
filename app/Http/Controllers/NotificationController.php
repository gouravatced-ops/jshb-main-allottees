<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();

        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found.'
            ], 404);
        }

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        $unreadCount = Notification::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('is_read', false)->orWhere('is_read', 0)->orWhereNull('is_read');
            })
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
            'unread_count' => $unreadCount,
            'notification' => $notification
        ]);
    }

    /**
     * Mark all unread notifications for the user as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('is_read', false)->orWhere('is_read', 0)->orWhereNull('is_read');
            })
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
            'unread_count' => 0
        ]);
    }
}
