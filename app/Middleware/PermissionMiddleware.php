<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string $resource,
        string $action
    ) {
        \Log::info("Checking permission for {$resource}:{$action}");
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $has = $user->hasPermission($resource, $action);

        if (!$has) {
            return response()->json([
                'message' => 'Forbidden'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}