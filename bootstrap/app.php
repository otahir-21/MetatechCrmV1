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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\DetectSubdomain::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\CheckEmployeeStatus::class,
        ]);
        
        $middleware->api(append: [
            \App\Http\Middleware\DetectSubdomain::class,
            \App\Http\Middleware\CheckUserStatus::class,
        ]);
        
        $middleware->alias([
            'subdomain.verify' => \App\Http\Middleware\VerifySubdomainAccess::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'project.access' => \App\Http\Middleware\CheckProjectAccess::class,
            'employee.status' => \App\Http\Middleware\CheckEmployeeStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
