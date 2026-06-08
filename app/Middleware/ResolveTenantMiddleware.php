<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Modules\Tenant\Models\Tenant;

class ResolveTenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Read subdomain and resolve tenant. Placeholder logic.
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = $parts[0] ?? null;

        if ($subdomain) {
            // In real implementation, resolve tenant from repository
            // $tenant = Tenant::where('slug', $subdomain)->first();
            $tenant = null;
            App::instance('currentTenant', $tenant);
        }

        return $next($request);
    }
}
