<?php

namespace App\Modules\Grading\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Answer\Resources\AnswerResource;
use App\Modules\Attempt\Resources\AttemptResource;
use App\Modules\Grading\Services\GradingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradingController extends Controller
{
    use ApiResponse;

    public function __construct(private GradingService $gradingService)
    {
    }

    public function pending(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $attempts = $this->gradingService->pending(
            $tenantId,
            (int) $request->query('page', 1),
            (int) $request->query('per_page', 15)
        );

        return response()->json($this->successResponse('Pending grading attempts retrieved', [
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
        ]));
    }

    public function reviewAnswer(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $validated = $request->validate([
            'manual_score' => ['required', 'integer', 'min:0'],
            'reviewer_feedback' => ['nullable', 'string'],
        ]);

        try {
            $result = $this->gradingService->reviewAnswer(
                $id,
                $tenantId,
                $request->user()->id,
                $validated['manual_score'],
                $validated['reviewer_feedback'] ?? null
            );

            return response()->json(
                $this->successResponse($result['message'], new AnswerResource($result['answer']))
            );
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }

    public function finalizeAttempt(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $result = $this->gradingService->finalizeAttempt($id, $tenantId, $request->user()->id);

            return response()->json(
                $this->successResponse($result['message'], new AttemptResource($result['attempt']))
            );
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }
}
