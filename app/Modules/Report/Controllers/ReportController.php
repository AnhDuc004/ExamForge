<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attempt\Resources\AttemptResource;
use App\Modules\Report\Services\ReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(private ReportService $reportService)
    {
    }

    public function testReport(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $result = $this->reportService->testReport(
                $id,
                $tenantId,
                (int) $request->query('page', 1),
                (int) $request->query('per_page', 15)
            );

            $attempts = $result['attempts'];

            return response()->json($this->successResponse('Test report retrieved', [
                'test' => $result['test'],
                'summary' => $result['summary'],
                'attempts' => [
                    'data' => AttemptResource::collection($attempts->items()),
                    'pagination' => [
                        'total' => $attempts->total(),
                        'count' => $attempts->count(),
                        'per_page' => $attempts->perPage(),
                        'current_page' => $attempts->currentPage(),
                        'last_page' => $attempts->lastPage(),
                        'from' => $attempts->firstItem(),
                        'to' => $attempts->lastItem(),
                    ],
                ],
            ]));
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }

    public function exportTestReport(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            return response()->json($this->successResponse(
                'Test report export completed',
                $this->reportService->exportTestReport($id, $tenantId, $request->user()->id)
            ), 202);
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }

    public function downloadJob(string $jobId, Request $request): BinaryFileResponse|JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $export = $this->reportService->resolveExport($jobId, $tenantId);
            $path = Storage::disk('local')->path($export->file_path);

            return response()->download($path, "test-report-{$export->test_id}.csv", [
                'Content-Type' => 'text/csv',
            ]);
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }
}
