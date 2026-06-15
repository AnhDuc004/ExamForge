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
        $tenantId = $request->query('tenant_id');
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

    public function show(string $id): JsonResponse
    {
        $role = $this->roleService->find($id);

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
        $dto = new CreateRoleDTO([
            'tenant_id' => $request->tenant_id,
            'name' => $request->name,
            'description' => $request->description,
            'permission_ids' => $request->permission_ids,
        ]);

        $role = $this->roleService->create($dto);

        return response()->json(
            $this->successResponse('Role created successfully', new RoleResource($role)),
            201
        );
    }

    public function update(string $id, UpdateRoleRequest $request): JsonResponse
    {
        $dto = new UpdateRoleDTO([
            'tenant_id' => $request->tenant_id,
            'name' => $request->name,
            'description' => $request->description,
            'permission_ids' => $request->permission_ids,
        ]);

        $role = $this->roleService->update($id, $dto);

        return response()->json(
            $this->successResponse('Role updated successfully', new RoleResource($role))
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->roleService->delete($id);

        return response()->json(
            $this->successResponse('Role deleted successfully')
        );
    }
}
