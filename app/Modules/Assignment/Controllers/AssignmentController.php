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
            $dto = new CreateAssignmentDTO(
                test_id: $request->test_id,
                assignee_id: $request->assignee_id,
                assignee_ids: $request->assignee_ids,
                assigned_by: $request->user()->id,
                access_type: $request->access_type,
                due_at: $request->due_at,
                max_attempts: $request->max_attempts ?? 1,
            );

            $result = $this->assignmentService->create($dto, $tenantId);

            return response()->json(
                $this->successResponse(
                    $result['message'],
                    array_filter([
                        'assignment' => isset($result['assignment']) ? new AssignmentResource($result['assignment']) : null,
                        'assignments' => isset($result['assignments']) ? AssignmentResource::collection($result['assignments']) : null,
                        'access_token' => $result['access_token'],
                        'access_tokens' => $result['access_tokens'] ?? null,
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

    public function show(string $id, Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;
            if (!$tenantId) {
                return response()->json($this->errorResponse('Tenant not resolved'), 400);
            }

            $result = $this->assignmentService->getById($id, $tenantId);

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

    public function selectableStudents(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);
        $search = $request->query('search');

        $students = $this->assignmentService->listSelectableStudents($tenantId, $page, $perPage, $search);

        return response()->json(
            $this->successResponse('Selectable students retrieved', [
                'data' => \App\Modules\User\Resources\UserResource::collection($students->items()),
                'pagination' => [
                    'total' => $students->total(),
                    'count' => $students->count(),
                    'per_page' => $students->perPage(),
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'from' => $students->firstItem(),
                    'to' => $students->lastItem(),
                ],
            ])
        );
    }

    public function my(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 15);
            $result = $this->assignmentService->listByAssigneeAndTenant($request->user()->id, $tenantId, $page, $perPage);

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
            $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;
            if (!$tenantId) {
                return response()->json($this->errorResponse('Tenant not resolved'), 400);
            }

            $dto = new UpdateAssignmentDTO([
                'due_at' => $request->due_at,
                'max_attempts' => $request->max_attempts,
                'status' => $request->status,
                'access_type' => $request->access_type,
            ]);

            $result = $this->assignmentService->update($id, $dto, $tenantId);

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

    public function destroy(string $id, Request $request): JsonResponse
    {
        try {
            $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;
            if (!$tenantId) {
                return response()->json($this->errorResponse('Tenant not resolved'), 400);
            }

            $this->assignmentService->delete($id, $tenantId);

            return response()->json($this->successResponse('Assignment deleted successfully'));
        } catch (\Throwable $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => ['required', 'string'],
        ]);

        try {
            $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;
            if (!$tenantId) {
                return response()->json($this->errorResponse('Tenant not resolved'), 400);
            }

            $result = $this->assignmentService->verifyAccessToken(hash('sha256', $validated['access_token']), $tenantId);

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

    public function verifyAccessToken(Request $request): JsonResponse
    {
        return $this->verify($request);
    }
}
