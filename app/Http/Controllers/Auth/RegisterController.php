<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm() { return view('auth.register'); }

    public function register(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users', 'password' => 'required|string|min:8|confirmed']);
        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'username' => strtolower(str_replace(' ', '.', $request->name)) . rand(100, 999)]);
        $role = Role::where('slug', 'sales-executive')->first();
        if ($role) $user->roles()->attach($role->id);
        Auth::login($user);
        return redirect()->route('admin.dashboard');
    }
}
