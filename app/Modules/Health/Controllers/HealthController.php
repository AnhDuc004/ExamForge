<?php

namespace App\Modules\Health\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $database = 'ok';
        $redis = 'ok';
        $disk = 'ok';

        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $database = 'error';
        }

        try {
            Redis::connection()->ping();
        } catch (\Throwable) {
            $redis = 'error';
        }

        if (!is_dir(storage_path()) || !is_writable(storage_path())) {
            $disk = 'error';
        }

        $status = $database === 'ok' && $redis === 'ok' && $disk === 'ok' ? 'ok' : 'degraded';

        return response()->json(
            $this->successResponse('Health check completed', [
                'status' => $status,
                'database' => $database,
                'redis' => $redis,
                'disk' => $disk,
                'checked_at' => now()->toIso8601String(),
            ])
        );
    }
}
