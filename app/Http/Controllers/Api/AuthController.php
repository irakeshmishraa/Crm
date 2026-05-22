<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) return response()->json(['message' => 'Invalid credentials'], 401);
        if ($user->status !== 'active') return response()->json(['message' => 'Account deactivated'], 403);
        return response()->json(['token' => $user->createToken('api-token')->plainTextToken, 'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->roles->first()?->name]]);
    }
    public function register(Request $request) { $request->validate(['name' => 'required|string', 'email' => 'required|email|unique:users', 'password' => 'required|string|min:8']); $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password)]); return response()->json(['token' => $user->createToken('api-token')->plainTextToken, 'user' => $user], 201); }
    public function logout(Request $request) { $request->user()->currentAccessToken()->delete(); return response()->json(['message' => 'Logged out']); }
    public function user(Request $request) { return response()->json($request->user()->load('roles')); }
}
