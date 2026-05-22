<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index() { return view('team.users.index', ['users' => User::with(['roles', 'department'])->paginate(25)]); }
    public function create() { return view('team.users.create', ['roles' => Role::all(), 'departments' => Department::where('is_active', true)->get(), 'managers' => User::where('status', 'active')->get()]); }
    public function store(Request $request) { $v = $request->validate(['name' => 'required|string', 'email' => 'required|email|unique:users', 'password' => 'required|string|min:8', 'phone' => 'nullable|string', 'designation' => 'nullable|string', 'department_id' => 'nullable|exists:departments,id', 'reporting_to' => 'nullable|exists:users,id', 'role_id' => 'required|exists:roles,id']); $user = User::create(['name' => $v['name'], 'email' => $v['email'], 'password' => Hash::make($v['password']), 'phone' => $v['phone'] ?? null, 'designation' => $v['designation'] ?? null, 'department_id' => $v['department_id'] ?? null, 'reporting_to' => $v['reporting_to'] ?? null, 'username' => strtolower(str_replace(' ', '.', $v['name'])) . rand(10, 99)]); $user->roles()->attach($v['role_id']); return redirect()->route('admin.users.index')->with('success', 'User created.'); }
    public function show(User $user) { $user->load(['roles', 'department', 'reportingTo', 'loginLogs' => fn($q) => $q->latest()->limit(10)]); return view('team.users.show', compact('user')); }
    public function edit(User $user) { return view('team.users.edit', ['user' => $user, 'roles' => Role::all(), 'departments' => Department::where('is_active', true)->get(), 'managers' => User::where('status', 'active')->where('id', '!=', $user->id)->get()]); }
    public function update(Request $request, User $user) { $v = $request->validate(['name' => 'required|string', 'email' => 'required|email|unique:users,email,' . $user->id, 'status' => 'required|in:active,inactive,suspended', 'role_id' => 'required|exists:roles,id']); $user->update($v); $user->roles()->sync([$v['role_id']]); return redirect()->route('admin.users.index')->with('success', 'Updated.'); }
    public function destroy(User $user) { $user->update(['status' => 'inactive']); return redirect()->route('admin.users.index')->with('success', 'User deactivated.'); }
}
