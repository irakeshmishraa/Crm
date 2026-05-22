<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->two_factor_enabled && session('2fa_required')) {
            return redirect()->route('two-factor.show');
        }
        return $next($request);
    }
}
