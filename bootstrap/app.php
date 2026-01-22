<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Admin;
use App\Http\Middleware\User;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SwitchDatabase;

ini_set('memory_limit', '1024M');

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'preventBackHistory'=>PreventBackHistory::class,
            // 'role' => RoleMiddleware::class,
            'admin' => Admin::class,
            'user' => User::class,
            // 'redirectIfNotAuthenticated' => RedirectIfNotAuthenticated::class
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'switchdb' => SwitchDatabase::class,
        ]);
        // Ensure DB switching happens before other middleware to avoid stale connections
        $middleware->prepend(SwitchDatabase::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
