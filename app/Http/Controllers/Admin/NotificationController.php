<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index() { return view('notifications.index', ['notifications' => Auth::user()->notifications()->paginate(25)]); }
    public function markRead(string $id) { Auth::user()->notifications()->where('id', $id)->update(['read_at' => now()]); return back(); }
    public function markAllRead() { Auth::user()->unreadNotifications->markAsRead(); return back()->with('success', 'All marked as read.'); }
    public function preferences() { return view('notifications.preferences', ['preferences' => Auth::user()->notification_preferences ?? []]); }
    public function updatePreferences(Request $request) { Auth::user()->update(['notification_preferences' => $request->preferences]); return back()->with('success', 'Preferences updated.'); }
}
