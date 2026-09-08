<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    public function read(string $id): RedirectResponse
    {
        $notification = DatabaseNotification::where('notifiable_id', Auth::id())
            ->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', '通知を既読にしました。');
    }
}
