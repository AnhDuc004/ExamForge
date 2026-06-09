<?php

namespace App\Modules\Assignment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assignment\DTOs\CreateAssignmentDTO;
use App\Modules\Assignment\DTOs\UpdateAssignmentDTO;
use App\Modules\Assignment\Requests\CreateAssignmentRequest;
use App\Modules\Assignment\Requests\UpdateAssignmentRequest;
use App\Modules\Assignment\Resources\AssignmentResource;
use App\Modules\Assignment\Services\AssignmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    use ApiResponse;

    public function __construct(private AssignmentService $assignmentService) {}

    public function store(CreateAssignmentRequest $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        try {
            $dto = new CreateAssignmentDTO([
                'test_id' => $request->test_id,
                'user_id' => $request->user_id,
                'due_date' => $request->due_date,
                'max_attempts' => $request->max_attempts ?? 1,
            ]);

            $result = $this->assignmentService->create($dto, $tenantId);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    [
                        'assignment' => new AssignmentResource($result['assignment']),
                        'access_token' => $result['access_token'],
                    ]
                ),
                201
            );
        } catch (\Exception $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function show(string $id, Request $request): JsonResponse
    {
        try {
            $result = $this->assignmentService->getById($id);

            return response()->json(
                $this->successResponse(
                    'Assignment retrieved',
                    new AssignmentResource($result['assignment'])
                ),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                $this->errorResponse('Assignment not found'),
                404
            );
        }
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json(
                $this->errorResponse('Tenant not resolved'),
                400
            );
        }

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 15);

        try {
            // If filtering by user
            if ($request->query('user_id')) {
                $userId = $request->query('user_id');
                $result = $this->assignmentService->listByUserAndTenant($userId, $tenantId, $page, $perPage);
            } else {
                $result = $this->assignmentService->listByTenant($tenantId, $page, $perPage);
            }

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    [
                        'data' => AssignmentResource::collection($result['assignments']->items()),
                        'pagination' => [
                            'total' => $result['assignments']->total(),
                            'count' => $result['assignments']->count(),
                            'per_page' => $result['assignments']->perPage(),
                            'current_page' => $result['assignments']->currentPage(),
                            'last_page' => $result['assignments']->lastPage(),
                            'from' => $result['assignments']->firstItem(),
                            'to' => $result['assignments']->lastItem(),
                        ],
                    ]
                ),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function update(string $id, UpdateAssignmentRequest $request): JsonResponse
    {
        try {
            $dto = new UpdateAssignmentDTO([
                'due_date' => $request->due_date,
                'max_attempts' => $request->max_attempts,
                'status' => $request->status,
            ]);

            $result = $this->assignmentService->update($id, $dto);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    new AssignmentResource($result['assignment'])
                ),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->assignmentService->delete($id);

            return response()->json(
                $this->successResponse('Assignment deleted successfully'),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function verifyAccessToken(Request $request): JsonResponse
    {
        $request->validate([
            'access_token' => ['required', 'string'],
        ]);

        try {
            $accessTokenHash = hash('sha256', $request->access_token);
            $result = $this->assignmentService->verifyAccessToken($accessTokenHash);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    new AssignmentResource($result['assignment'])
                ),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }
}
