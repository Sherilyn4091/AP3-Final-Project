<?php

// bootstrap/app.php

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
        // Trust all proxies for Render
        $middleware->trustProxies(at: '*', headers: [
            'X-Forwarded-For',
            'X-Forwarded-Host',
            'X-Forwarded-Proto',
            'X-Forwarded-Port',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();