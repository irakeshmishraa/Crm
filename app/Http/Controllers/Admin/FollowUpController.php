<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = FollowUp::with(['lead', 'assignedTo']);
        if (!$user->isAdmin()) $query->where('assigned_to', $user->id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('date')) $query->whereDate('scheduled_at', $request->date);
        return view('followups.index', ['followups' => $query->orderBy('scheduled_at')->paginate(25)]);
    }

    public function create(Request $request) { return view('followups.create', ['leads' => Lead::whereNotIn('status', ['won', 'lost'])->orderBy('name')->get(), 'selectedLead' => $request->lead_id ? Lead::find($request->lead_id) : null]); }

    public function store(Request $request)
    {
        $v = $request->validate(['lead_id' => 'required|exists:leads,id', 'type' => 'required|string', 'title' => 'required|string|max:255', 'description' => 'nullable|string', 'scheduled_at' => 'required|date', 'assigned_to' => 'nullable|exists:users,id', 'is_recurring' => 'boolean', 'recurrence_pattern' => 'nullable|string', 'reminder_minutes_before' => 'nullable|integer', 'reminder_channels' => 'nullable|array']);
        $v['created_by'] = Auth::id();
        $v['assigned_to'] = $v['assigned_to'] ?? Auth::id();
        $f = FollowUp::create($v);
        $lead = Lead::find($v['lead_id']);
        if ($lead->status === 'new') $lead->update(['status' => 'follow_up']);
        LeadActivity::create(['lead_id' => $v['lead_id'], 'user_id' => Auth::id(), 'type' => 'followup_created', 'title' => 'Follow-up Scheduled', 'description' => "{$v['type']} for " . $f->scheduled_at->format('d M Y h:i A')]);
        return redirect()->route('admin.followups.index')->with('success', 'Follow-up scheduled.');
    }

    public function show(FollowUp $followup) { $followup->load(['lead', 'assignedTo', 'createdBy']); return view('followups.show', compact('followup')); }
    public function edit(FollowUp $followup) { return view('followups.edit', ['followup' => $followup, 'leads' => Lead::orderBy('name')->get()]); }
    public function update(Request $request, FollowUp $followup) { $followup->update($request->validate(['type' => 'required|string', 'title' => 'required|string|max:255', 'description' => 'nullable|string', 'scheduled_at' => 'required|date', 'notes' => 'nullable|string'])); return redirect()->route('admin.followups.show', $followup)->with('success', 'Updated.'); }
    public function destroy(FollowUp $followup) { $followup->delete(); return redirect()->route('admin.followups.index')->with('success', 'Deleted.'); }

    public function markComplete(Request $request, FollowUp $followup)
    {
        $followup->update(['status' => 'completed', 'completed_at' => now(), 'outcome' => $request->outcome, 'notes' => $request->notes]);
        $followup->lead->update(['last_contacted_at' => now()]);
        LeadActivity::create(['lead_id' => $followup->lead_id, 'user_id' => Auth::id(), 'type' => 'followup_completed', 'title' => 'Follow-up Completed']);
        return back()->with('success', 'Marked as completed.');
    }

    public function snooze(Request $request, FollowUp $followup) { $request->validate(['minutes' => 'required|integer|min:5']); $followup->update(['scheduled_at' => $followup->scheduled_at->addMinutes($request->minutes), 'reminder_sent' => false]); return back()->with('success', 'Snoozed.'); }
    public function reschedule(Request $request, FollowUp $followup) { $request->validate(['scheduled_at' => 'required|date']); $followup->update(['scheduled_at' => $request->scheduled_at, 'status' => 'rescheduled', 'reminder_sent' => false]); return back()->with('success', 'Rescheduled.'); }
    public function calendar() { $user = Auth::user(); $followups = FollowUp::with('lead')->when(!$user->isAdmin(), fn($q) => $q->where('assigned_to', $user->id))->get()->map(fn($f) => ['id' => $f->id, 'title' => $f->title . ' - ' . ($f->lead->name ?? ''), 'start' => $f->scheduled_at->toIso8601String(), 'color' => $f->status === 'completed' ? '#10b981' : ($f->isOverdue() ? '#ef4444' : '#3b82f6'), 'url' => route('admin.followups.show', $f)]); return view('followups.calendar', compact('followups')); }
    public function kanban() { $user = Auth::user(); $followups = FollowUp::with('lead')->when(!$user->isAdmin(), fn($q) => $q->where('assigned_to', $user->id))->where('status', '!=', 'cancelled')->orderBy('scheduled_at')->get()->groupBy('status'); return view('followups.kanban', compact('followups')); }
}
