<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/gmail.send', 'https://www.googleapis.com/auth/gmail.readonly', 'https://www.googleapis.com/auth/spreadsheets', 'https://www.googleapis.com/auth/calendar'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])->redirect();
    }

    public function callback()
    {
        try {
            $g = Socialite::driver('google')->user();
            $user = User::where('google_id', $g->getId())->orWhere('email', $g->getEmail())->first();
            if ($user) {
                $user->update(['google_id' => $g->getId(), 'google_token' => $g->token, 'google_refresh_token' => $g->refreshToken ?? $user->google_refresh_token, 'avatar' => $g->getAvatar()]);
            } else {
                $user = User::create(['name' => $g->getName(), 'email' => $g->getEmail(), 'google_id' => $g->getId(), 'google_token' => $g->token, 'google_refresh_token' => $g->refreshToken, 'avatar' => $g->getAvatar(), 'password' => bcrypt(str()->random(24)), 'email_verified_at' => now()]);
                $role = Role::where('slug', 'sales-executive')->first();
                if ($role) $user->roles()->attach($role->id);
            }
            Auth::login($user, true);
            return redirect()->route('admin.dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'Google authentication failed.']);
        }
    }
}
