<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index() { return view('team.roles.index', ['roles' => Role::withCount(['users', 'permissions'])->get()]); }
    public function create() { return view('team.roles.create', ['permissions' => Permission::all()->groupBy('module')]); }
    public function store(Request $request) { $request->validate(['name' => 'required|string|unique:roles', 'permissions' => 'required|array']); $role = Role::create(['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description]); $role->permissions()->sync($request->permissions); return redirect()->route('admin.roles.index')->with('success', 'Role created.'); }
    public function edit(Role $role) { return view('team.roles.edit', ['role' => $role, 'permissions' => Permission::all()->groupBy('module'), 'rolePermissions' => $role->permissions->pluck('id')->toArray()]); }
    public function update(Request $request, Role $role) { if ($role->is_system) return back()->withErrors(['role' => 'System roles cannot be modified.']); $request->validate(['name' => 'required|string|unique:roles,name,' . $role->id, 'permissions' => 'required|array']); $role->update(['name' => $request->name, 'slug' => Str::slug($request->name), 'description' => $request->description]); $role->permissions()->sync($request->permissions); return redirect()->route('admin.roles.index')->with('success', 'Updated.'); }
    public function destroy(Role $role) { if ($role->is_system) return back()->withErrors(['role' => 'Cannot delete system roles.']); $role->delete(); return redirect()->route('admin.roles.index')->with('success', 'Deleted.'); }
}
