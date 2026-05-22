<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit() { return view('settings.profile', ['user' => Auth::user()]); }
    public function update(Request $request) { $v = $request->validate(['name' => 'required|string', 'email' => 'required|email|unique:users,email,' . Auth::id(), 'phone' => 'nullable|string', 'timezone' => 'nullable|string']); if ($request->hasFile('avatar')) $v['avatar'] = $request->file('avatar')->store('avatars', 'public'); Auth::user()->update($v); return back()->with('success', 'Profile updated.'); }
    public function updatePassword(Request $request) { $request->validate(['current_password' => 'required', 'password' => 'required|min:8|confirmed']); if (!Hash::check($request->current_password, Auth::user()->password)) return back()->withErrors(['current_password' => 'Incorrect.']); Auth::user()->update(['password' => Hash::make($request->password)]); return back()->with('success', 'Password updated.'); }
}
