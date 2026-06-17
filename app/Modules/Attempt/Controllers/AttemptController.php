<?php

namespace App\Modules\Attempt\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Answer\Resources\AnswerResource;
use App\Modules\Attempt\Resources\AttemptResource;
use App\Modules\Attempt\Services\AttemptService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    use ApiResponse;

    public function __construct(private AttemptService $attemptService)
    {
    }

    public function start(string $assignmentId, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $result = $this->attemptService->start($assignmentId, $request->user()->id, $tenantId);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    new AttemptResource($result['attempt'])
                ),
                201
            );
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $result = $this->attemptService->show($id, $request->user()->id, $tenantId);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    new AttemptResource($result['attempt'])
                )
            );
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }

    public function saveAnswer(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $validated = $request->validate([
            'test_section_question_id' => ['required', 'uuid'],
            'response' => ['nullable', 'array'],
        ]);

        try {
            $result = $this->attemptService->saveAnswer(
                $id,
                $request->user()->id,
                $tenantId,
                $validated['test_section_question_id'],
                $validated['response'] ?? null
            );

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    new AnswerResource($result['answer'])
                )
            );
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }

    public function submit(string $id, Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $result = $this->attemptService->submit($id, $request->user()->id, $tenantId);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    new AttemptResource($result['attempt'])
                )
            );
        } catch (\Throwable $e) {
            return response()->json($this->errorResponse($e->getMessage()), 400);
        }
    }
}
