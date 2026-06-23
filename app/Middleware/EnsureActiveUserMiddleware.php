<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveUserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error_code' => 'UNAUTHENTICATED',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is inactive',
                'error_code' => 'ACCOUNT_INACTIVE',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
