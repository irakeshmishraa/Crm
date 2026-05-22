<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request) { $user = Auth::user(); $q = Task::with(['assignedTo', 'lead', 'client']); if (!$user->isAdmin()) $q->where('assigned_to', $user->id); if ($request->filled('status')) $q->where('status', $request->status); if ($request->filled('priority')) $q->where('priority', $request->priority); return view('tasks.index', ['tasks' => $q->orderBy('due_date')->paginate(25)]); }
    public function create() { return view('tasks.create', ['users' => User::where('status', 'active')->get()]); }
    public function store(Request $request) { $v = $request->validate(['title' => 'required|string', 'description' => 'nullable|string', 'assigned_to' => 'required|exists:users,id', 'lead_id' => 'nullable|exists:leads,id', 'client_id' => 'nullable|exists:clients,id', 'priority' => 'required|in:low,medium,high,urgent', 'due_date' => 'nullable|date']); $v['created_by'] = Auth::id(); Task::create($v); return redirect()->route('admin.tasks.index')->with('success', 'Task created.'); }
    public function show(Task $task) { $task->load(['assignedTo', 'createdBy', 'comments.user', 'lead', 'client']); return view('tasks.show', compact('task')); }
    public function edit(Task $task) { return view('tasks.edit', ['task' => $task, 'users' => User::where('status', 'active')->get()]); }
    public function update(Request $request, Task $task) { $v = $request->validate(['title' => 'required|string', 'assigned_to' => 'required|exists:users,id', 'priority' => 'required|in:low,medium,high,urgent', 'status' => 'required|in:pending,in_progress,completed,cancelled', 'due_date' => 'nullable|date']); if ($v['status'] === 'completed') $v['completed_at'] = now(); $task->update($v); return redirect()->route('admin.tasks.show', $task)->with('success', 'Updated.'); }
    public function destroy(Task $task) { $task->delete(); return redirect()->route('admin.tasks.index')->with('success', 'Deleted.'); }
    public function markComplete(Task $task) { $task->update(['status' => 'completed', 'completed_at' => now()]); return back()->with('success', 'Task completed.'); }
}
