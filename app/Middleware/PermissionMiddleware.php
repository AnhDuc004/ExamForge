<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Expected format: permission:resource,action
     */
    public function handle(Request $request, Closure $next, string $permission = null)
    {
        if (!$permission) {
            return $next($request);
        }

        [$resource, $action] = array_map('trim', explode(',', $permission));

        // Resolve current user and check permissions via trait/service (placeholder)
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // In real implementation, call $user->hasPermission($resource, $action)
        $has = false;

        if (!$has) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
