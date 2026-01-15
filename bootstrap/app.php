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
        // Trust all proxies for Render deployment
        $middleware->trustProxies(at: '*');
        
        // Note: In Laravel 11, web middleware (sessions, CSRF, cookies) 
        // is automatically included by default. No need to append manually.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();