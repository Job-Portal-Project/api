<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registering middleware aliases
        $middleware->alias([
            'localization' => \App\Http\Middleware\LocalizationMiddleware::class,
            'jwt.access' => \App\Http\Middleware\JWT\AccessTokenMiddleware::class,
            'jwt.refresh' => \App\Http\Middleware\JWT\RefreshTokenMiddleware::class,
        ]);

        // Defining middleware priority
        $middleware->priority([
            \App\Http\Middleware\LocalizationMiddleware::class,
            \App\Http\Middleware\JWT\AccessTokenMiddleware::class,
            \App\Http\Middleware\JWT\RefreshTokenMiddleware::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle global exception configuration (if needed)
    })
    ->create();
