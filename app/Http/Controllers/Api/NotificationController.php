<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * NotificationController — expose les notifications in-app au HR connecté.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->latest()->get()
        );
    }

    public function unread(Request $request)
    {
        return response()->json(
            $request->user()->unreadNotifications()->latest()->get()
        );
    }

    public function markAsRead(Request $request, string $notificationId)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->json(['status' => 'marked_as_read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['status' => 'all_marked_as_read']);
    }
}
