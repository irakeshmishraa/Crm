<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index() { return view('team.index', ['departments' => Department::with(['users', 'head'])->get(), 'users' => User::with(['department', 'roles'])->where('status', 'active')->get()]); }
    public function hierarchy() { return view('team.hierarchy', ['users' => User::with(['reportingTo', 'subordinates', 'department'])->where('status', 'active')->get()]); }
    public function performance(Request $request) { $users = User::where('status', 'active')->withCount(['assignedLeads as total_leads', 'assignedLeads as won_leads' => fn($q) => $q->where('status', 'won'), 'followUps as completed_followups' => fn($q) => $q->where('status', 'completed')])->get(); return view('team.performance', compact('users')); }
    public function activityLogs(Request $request) { $logs = ActivityLog::with('user')->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))->latest()->paginate(50); return view('team.activity-logs', compact('logs')); }
}
