<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function show() { return view('auth.two-factor'); }
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $google2fa = app('pragmarx.google2fa');
        if ($google2fa->verifyKey(Auth::user()->two_factor_secret, $request->code)) {
            session()->forget('2fa_required');
            return redirect()->intended(route('admin.dashboard'));
        }
        return back()->withErrors(['code' => 'Invalid verification code.']);
    }
    public function enable(Request $request)
    {
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();
        Auth::user()->update(['two_factor_secret' => $secret, 'two_factor_enabled' => true]);
        return response()->json(['secret' => $secret, 'qr_code_url' => $google2fa->getQRCodeUrl(config('app.name'), Auth::user()->email, $secret)]);
    }
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required']);
        if (!password_verify($request->password, Auth::user()->password)) return back()->withErrors(['password' => 'Invalid password.']);
        Auth::user()->update(['two_factor_enabled' => false, 'two_factor_secret' => null]);
        return back()->with('success', 'Two-factor authentication disabled.');
    }
}
