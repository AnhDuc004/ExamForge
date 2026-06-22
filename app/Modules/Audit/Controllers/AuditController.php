<?php

namespace App\Modules\Audit\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Models\AuditLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);

        $query = AuditLog::query()
            ->with('actor')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->query('resource_type'));
        }

        $logs = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $logs->getCollection()->map(function (AuditLog $log) {
            return [
                'id' => $log->id,
                'tenant_id' => $log->tenant_id,
                'actor_id' => $log->actor_id,
                'actor' => $log->actor ? [
                    'id' => $log->actor->id,
                    'email' => $log->actor->email,
                    'display_name' => $log->actor->display_name,
                ] : null,
                'action' => $log->action,
                'resource_type' => $log->resource_type,
                'resource_id' => $log->resource_id,
                'ip' => $log->metadata['ip'] ?? null,
                'metadata' => $log->metadata,
                'created_at' => $log->created_at,
            ];
        });

        return response()->json(
            $this->successResponse('Audit logs retrieved', [
                'data' => $data->values(),
                'pagination' => [
                    'total' => $logs->total(),
                    'count' => $logs->count(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
            ])
        );
    }
}
