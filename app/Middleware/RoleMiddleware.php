<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED',
                'data' => null,
                'errors' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        $roleList = array_values(array_filter(array_map('trim', $roles)));

        if (empty($roleList) || !$user->hasAnyRole($roleList)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'error_code' => 'FORBIDDEN',
                'data' => null,
                'errors' => null,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
