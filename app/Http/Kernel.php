<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Middleware\ResolveTenantMiddleware;
use App\Middleware\PermissionMiddleware;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // Global middleware
        \App\Middleware\ResolveTenantMiddleware::class,
    ];

    protected $routeMiddleware = [
        'permission' => PermissionMiddleware::class,
    ];
}
