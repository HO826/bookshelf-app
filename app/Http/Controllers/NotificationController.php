<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // 通知一覧表示
    public function index()
    {
        $notifications = Auth::user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    // 既読処理
    public function read($id)
    {
        $notification = DatabaseNotification::where('notifiable_id', Auth::id())
            ->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', '通知を既読にしました。');
    }
}
