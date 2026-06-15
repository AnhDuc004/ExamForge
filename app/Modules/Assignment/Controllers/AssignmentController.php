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

    public function __construct(private AssignmentService $assignmentService)
    {
    }

    public function store(CreateAssignmentRequest $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $dto = new CreateAssignmentDTO([
                'test_id' => $request->test_id,
                'assignee_id' => $request->assignee_id,
                'assigned_by' => $request->user()->id,
                'access_type' => $request->access_type,
                'due_at' => $request->due_at,
                'max_attempts' => $request->max_attempts ?? 1,
            ]);

            $result = $this->assignmentService->create($dto, $tenantId);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    array_filter([
                        'assignment' => new AssignmentResource($result['assignment']),
                        'access_token' => $result['access_token'],
                    ], fn ($value) => $value !== null)
                ),
                201
            );
        } catch (\Throwable $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $result = $this->assignmentService->getById($id);

            return response()->json(
                $this->successResponse(
                    'Assignment retrieved',
                    new AssignmentResource($result['assignment'])
                )
            );
        } catch (\Throwable $e) {
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
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);
        $assigneeId = $request->query('assignee_id', $request->query('user_id'));
        $assignedById = $request->query('assigned_by_id');

        try {
            if ($assigneeId) {
                $result = $this->assignmentService->listByAssigneeAndTenant($assigneeId, $tenantId, $page, $perPage);
            } elseif ($assignedById) {
                $result = $this->assignmentService->listByAssignedByAndTenant($assignedById, $tenantId, $page, $perPage);
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
                )
            );
        } catch (\Throwable $e) {
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
                'due_at' => $request->due_at,
                'max_attempts' => $request->max_attempts,
                'status' => $request->status,
                'access_type' => $request->access_type,
            ]);

            $result = $this->assignmentService->update($id, $dto);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    array_filter([
                        'assignment' => new AssignmentResource($result['assignment']),
                        'access_token' => $result['access_token'],
                    ], fn ($value) => $value !== null)
                )
            );
        } catch (\Throwable $e) {
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

            return response()->json($this->successResponse('Assignment deleted successfully'));
        } catch (\Throwable $e) {
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
            $result = $this->assignmentService->verifyAccessToken(hash('sha256', $request->access_token));

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    new AssignmentResource($result['assignment'])
                )
            );
        } catch (\Throwable $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }
}
