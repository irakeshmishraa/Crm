<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        Blade::directive('currency', function ($amount) { return "<?php echo format_currency($amount); ?>"; });
        Blade::directive('role', function ($role) { return "<?php if(auth()->check() && auth()->user()->hasRole($role)): ?>"; });
        Blade::directive('endrole', function () { return "<?php endif; ?>"; });
        Blade::directive('permission', function ($p) { return "<?php if(auth()->check() && auth()->user()->hasPermission($p)): ?>"; });
        Blade::directive('endpermission', function () { return "<?php endif; ?>"; });
    }
}
