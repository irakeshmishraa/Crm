<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'two-factor' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'lead-capture/*',
            'quotation/*/approve',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
