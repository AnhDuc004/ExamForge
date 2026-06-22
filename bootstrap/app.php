<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\ResolveTenantMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => null);
        $middleware->alias([
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'tenant' => ResolveTenantMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED',
                'data' => null,
                'errors' => null,
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'error_code' => 'FORBIDDEN',
                'data' => null,
                'errors' => null,
            ], Response::HTTP_FORBIDDEN);
        });
    })
    ->create();
