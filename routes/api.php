<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // PUBLIC (no tenant)
    require __DIR__ . '/modules/auth.php';
    require __DIR__ . '/modules/assignment_public.php';

    // PROTECTED (global, no tenant resolution)
    Route::middleware([
        'api',
        'auth:sanctum',
        \App\Middleware\EnsureActiveUserMiddleware::class,
    ])->group(function () {
        require __DIR__ . '/modules/tenant.php';
    });

    // PROTECTED (tenant-based)
    Route::middleware([
        'api',
        \App\Middleware\ResolveTenantMiddleware::class,
        'auth:sanctum',
        \App\Middleware\EnsureActiveUserMiddleware::class,
    ])->group(function () {

        require __DIR__ . '/modules/user.php';
        require __DIR__ . '/modules/role.php';
        require __DIR__ . '/modules/permission.php';
        require __DIR__ . '/modules/question.php';
        require __DIR__ . '/modules/assignment.php';
        require __DIR__ . '/modules/attempt.php';
        require __DIR__ . '/modules/grading.php';
        require __DIR__ . '/modules/report.php';
        require __DIR__ . '/modules/audit.php';
        require __DIR__ . '/modules/ai.php';
        require __DIR__ . '/modules/health.php';
        require __DIR__ . '/modules/test.php';
    });
});
