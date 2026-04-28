<?php

use App\Http\Middleware\EnsureCustomer;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureTenantUsable;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant.usable' => EnsureTenantUsable::class,
            'role' => EnsureRole::class,
            'customer.portal' => EnsureCustomer::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhooks/pakasir/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
