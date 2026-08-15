<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notificationsQuery()
            ->latest()
            ->paginate(10);

        return response()->view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotificationsCount(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Notification $notification): Response
    {
        abort_unless($request->user()->is($notification->user), 403);

        if ($notification->shouldMarkAsReadOnView()) {
            $notification->markAsRead();
        }

        return response()->view('notifications.show', [
            'notification' => $notification,
            'unreadCount' => $request->user()->unreadNotificationsCount(),
        ]);
    }

    /**
     * Mark the specified resource as read.
     */
    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($request->user()->is($notification->user), 403);

        $notification->markAsRead();

        return back();
    }

    /**
     * Mark the specified resource as unread.
     */
    public function markAsUnread(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($request->user()->is($notification->user), 403);

        $notification->markAsUnread();

        return back();
    }
}
