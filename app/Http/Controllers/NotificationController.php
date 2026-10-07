<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Notification $notification)
    {
        if ($notification->user_id !==  Auth::id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return redirect()->route('notifications.index');
    }

    public function readAll()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user
            ->notifications()
            ->unread()
            ->update(['is_read' => true]);

        return redirect()->back()->with('success', 'تم تحديد كل الإشعارات كمقروءة.');
    }
}