<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsurePlanLimits;
use App\Http\Middleware\EnsureUserBelongsToTenant;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\CheckSubscription;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.identify' => IdentifyTenant::class,
            'tenant.user' => EnsureUserBelongsToTenant::class,
            'tenant.plan' => EnsurePlanLimits::class,
            'subscription' => CheckSubscription::class,
        ]);

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
