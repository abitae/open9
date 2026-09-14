<?php

use App\Http\Middleware\EnsureClientEmailIsVerified;
use App\Http\Middleware\EnsureClientIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // El state anti-CSRF de OAuth (ver GoogleAuthController) es un valor
        // opaco de un solo uso, equivalente en sensibilidad al `state` que ya
        // viaja en claro por la URL; se excluye de cifrado para poder leerlo
        // igual en la ruta `api` que lo emite y en la que lo valida.
        $middleware->encryptCookies(except: ['oauth_state']);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'client.active' => EnsureClientIsActive::class,
            'client.verified' => EnsureClientEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
