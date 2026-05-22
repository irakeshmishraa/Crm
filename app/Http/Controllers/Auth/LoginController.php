<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function showLoginForm() { return view('auth.login'); }

    public function login(Request $request)
    {
        $request->validate(['login' => 'required|string', 'password' => 'required|string']);
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['login' => 'Too many attempts. Try again in ' . RateLimiter::availableIn($key) . 's.']);
        }
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (Auth::attempt([$field => $request->login, 'password' => $request->password], $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $user = Auth::user();
            if ($user->status !== 'active') { Auth::logout(); return back()->withErrors(['login' => 'Account deactivated.']); }
            $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
            LoginLog::create(['user_id' => $user->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'status' => 'success']);
            $request->session()->regenerate();
            if ($user->two_factor_enabled) { session(['2fa_required' => true]); return redirect()->route('two-factor.show'); }
            return redirect()->intended(route('admin.dashboard'));
        }
        RateLimiter::hit($key, 900);
        return back()->withErrors(['login' => 'Invalid credentials.'])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
