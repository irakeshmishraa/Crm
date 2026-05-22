<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index() { return view('calendar.index'); }
    public function events(Request $request) {
        $user = Auth::user();
        $events = CalendarEvent::where('user_id', $user->id)->when($request->start, fn($q) => $q->where('start_at', '>=', $request->start))->when($request->end, fn($q) => $q->where('start_at', '<=', $request->end))->get()->map(fn($e) => ['id' => 'event_' . $e->id, 'title' => $e->title, 'start' => $e->start_at->toIso8601String(), 'end' => $e->end_at?->toIso8601String(), 'color' => $e->color, 'allDay' => $e->all_day]);
        $followups = FollowUp::with('lead')->where('assigned_to', $user->id)->when($request->start, fn($q) => $q->where('scheduled_at', '>=', $request->start))->get()->map(fn($f) => ['id' => 'fu_' . $f->id, 'title' => $f->title . ' - ' . ($f->lead->name ?? ''), 'start' => $f->scheduled_at->toIso8601String(), 'color' => $f->status === 'completed' ? '#10b981' : '#f59e0b', 'url' => route('admin.followups.show', $f)]);
        return response()->json($events->merge($followups));
    }
    public function storeEvent(Request $request) { $v = $request->validate(['title' => 'required|string', 'start_at' => 'required|date', 'end_at' => 'nullable|date', 'all_day' => 'boolean', 'color' => 'nullable|string']); $v['user_id'] = Auth::id(); CalendarEvent::create($v); return response()->json(['success' => true]); }
}
