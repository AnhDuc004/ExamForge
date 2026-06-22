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
        $host = $request->getHost(); 
        $subdomain = null;

        if (!filter_var($host, FILTER_VALIDATE_IP) && !in_array($host, ['localhost', '127.0.0.1'], true)) {
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $subdomain = $parts[0] ?? null;
            }
        }

        $tenant = null;

        if ($subdomain) {
            $tenant = Tenant::where('slug', $subdomain)
                ->where('is_active', true)
                ->first();
        }

        if (!$tenant) {
            $tenantId = $request->header('X-Tenant-ID');

            if ($tenantId) {
                $tenant = Tenant::where('id', $tenantId)
                    ->where('is_active', true)
                    ->first();
            }
        }

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
                'error_code' => 'TENANT_NOT_FOUND',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        App::instance('currentTenant', $tenant);

        return $next($request);
    }
}
