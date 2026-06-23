<?php

namespace App\Modules\Permission\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Permission\DTOs\CreatePermissionDTO;
use App\Modules\Permission\DTOs\UpdatePermissionDTO;
use App\Modules\Permission\Requests\CreatePermissionRequest;
use App\Modules\Permission\Requests\UpdatePermissionRequest;
use App\Modules\Permission\Resources\PermissionResource;
use App\Modules\Permission\Services\PermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct(private PermissionService $permissionService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);

        $permissions = $this->permissionService->list($page, $perPage);

        return response()->json(
            $this->successResponse('Permissions retrieved', [
                'data' => PermissionResource::collection($permissions->items()),
                'pagination' => [
                    'total' => $permissions->total(),
                    'count' => $permissions->count(),
                    'per_page' => $permissions->perPage(),
                    'current_page' => $permissions->currentPage(),
                    'last_page' => $permissions->lastPage(),
                    'from' => $permissions->firstItem(),
                    'to' => $permissions->lastItem(),
                ],
            ])
        );
    }

    public function show(string $id): JsonResponse
    {
        $permission = $this->permissionService->find($id);

        if (!$permission) {
            return response()->json(
                $this->errorResponse('Permission not found'),
                404
            );
        }

        return response()->json(
            $this->successResponse('Permission retrieved', new PermissionResource($permission))
        );
    }

    public function store(CreatePermissionRequest $request): JsonResponse
    {
        if (!$request->user()?->hasRole('System Admin')) {
            return response()->json(
                $this->errorResponse('Only System Admin can manage the global permission catalog.', null, 'FORBIDDEN'),
                Response::HTTP_FORBIDDEN
            );
        }

        $dto = new CreatePermissionDTO([
            'resource' => $request->resource,
            'action' => $request->action,
        ]);

        $permission = $this->permissionService->create($dto);

        return response()->json(
            $this->successResponse('Permission created successfully', new PermissionResource($permission)),
            201
        );
    }

    public function update(string $id, UpdatePermissionRequest $request): JsonResponse
    {
        if (!$request->user()?->hasRole('System Admin')) {
            return response()->json(
                $this->errorResponse('Only System Admin can manage the global permission catalog.', null, 'FORBIDDEN'),
                Response::HTTP_FORBIDDEN
            );
        }

        $dto = new UpdatePermissionDTO([
            'resource' => $request->resource,
            'action' => $request->action,
        ]);

        $permission = $this->permissionService->update($id, $dto);

        return response()->json(
            $this->successResponse('Permission updated successfully', new PermissionResource($permission))
        );
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        if (!$request->user()?->hasRole('System Admin')) {
            return response()->json(
                $this->errorResponse('Only System Admin can manage the global permission catalog.', null, 'FORBIDDEN'),
                Response::HTTP_FORBIDDEN
            );
        }

        $this->permissionService->delete($id);

        return response()->json(
            $this->successResponse('Permission deleted successfully')
        );
    }
}
