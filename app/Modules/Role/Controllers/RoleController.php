<?php

namespace App\Modules\Role\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Role\DTOs\CreateRoleDTO;
use App\Modules\Role\DTOs\UpdateRoleDTO;
use App\Modules\Role\Requests\CreateRoleRequest;
use App\Modules\Role\Requests\UpdateRoleRequest;
use App\Modules\Role\Resources\RoleResource;
use App\Modules\Role\Services\RoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(private RoleService $roleService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);

        $roles = $this->roleService->list($tenantId, $page, $perPage);

        return response()->json(
            $this->successResponse('Roles retrieved', [
                'data' => RoleResource::collection($roles->items()),
                'pagination' => [
                    'total' => $roles->total(),
                    'count' => $roles->count(),
                    'per_page' => $roles->perPage(),
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'from' => $roles->firstItem(),
                    'to' => $roles->lastItem(),
                ],
            ])
        );
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $role = $this->roleService->find($id, $tenantId);

        if (!$role) {
            return response()->json(
                $this->errorResponse('Role not found'),
                404
            );
        }

        return response()->json(
            $this->successResponse('Role retrieved', new RoleResource($role))
        );
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new CreateRoleDTO([
            'name' => $request->name,
            'description' => $request->description,
            'permission_ids' => $request->permission_ids,
        ]);

        $role = $this->roleService->create($dto, $tenantId);

        return response()->json(
            $this->successResponse('Role created successfully', new RoleResource($role)),
            201
        );
    }

    public function update(string $id, UpdateRoleRequest $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new UpdateRoleDTO([
            'name' => $request->name,
            'description' => $request->description,
            'permission_ids' => $request->permission_ids,
        ]);

        $role = $this->roleService->update($id, $dto, $tenantId);

        return response()->json(
            $this->successResponse('Role updated successfully', new RoleResource($role))
        );
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $this->roleService->delete($id, $tenantId);

        return response()->json(
            $this->successResponse('Role deleted successfully')
        );
    }

    private function resolveCurrentTenantId(Request $request): ?string
    {
        return app()->bound('currentTenant')
            ? app('currentTenant')?->id
            : $request->header('X-Tenant-ID');
    }
}
