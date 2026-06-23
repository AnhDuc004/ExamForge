<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\User\Requests\CreateUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;
use App\Modules\User\Requests\UpdateUserStatusRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private UserService $userService)
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
        $search = $request->query('search');

        $users = $this->userService->list($tenantId, $page, $perPage, $search);

        return response()->json(
            $this->successResponse('Users retrieved', [
                'data' => UserResource::collection($users->items()),
                'pagination' => [
                    'total' => $users->total(),
                    'count' => $users->count(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
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

        $user = $this->userService->find($id, $tenantId);

        if (!$user) {
            return response()->json(
                $this->errorResponse('User not found'),
                404
            );
        }

        return response()->json(
            $this->successResponse('User retrieved', new UserResource($user))
        );
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        $dto = new CreateUserDTO([
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password' => $request->password,
            'is_active' => $request->boolean('is_active', true),
            'role_ids' => $request->role_ids,
        ]);

        $user = $this->userService->create($dto, $tenantId);

        return response()->json(
            $this->successResponse('User created successfully', new UserResource($user)),
            201
        );
    }

    public function update(string $id, UpdateUserRequest $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        if ($request->user()?->id === $id && $request->has('is_active') && !$request->boolean('is_active')) {
            return response()->json(
                $this->errorResponse('You cannot deactivate your own account.', null, 'SELF_DEACTIVATION_FORBIDDEN'),
                Response::HTTP_FORBIDDEN
            );
        }

        $dto = new UpdateUserDTO([
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password' => $request->password,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
            'role_ids' => $request->role_ids,
        ]);

        $user = $this->userService->update($id, $dto, $tenantId);

        return response()->json(
            $this->successResponse('User updated successfully', new UserResource($user))
        );
    }

    public function updateStatus(string $id, UpdateUserStatusRequest $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        if ($request->user()?->id === $id && !$request->boolean('is_active')) {
            return response()->json(
                $this->errorResponse('You cannot deactivate your own account.', null, 'SELF_DEACTIVATION_FORBIDDEN'),
                Response::HTTP_FORBIDDEN
            );
        }

        $user = $this->userService->updateStatus($id, $request->boolean('is_active'), $tenantId);

        return response()->json(
            $this->successResponse('User status updated successfully', new UserResource($user))
        );
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        $tenantId = $this->resolveCurrentTenantId($request);
        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        if ($request->user()?->id === $id) {
            return response()->json(
                $this->errorResponse('You cannot delete your own account.', null, 'SELF_DELETE_FORBIDDEN'),
                Response::HTTP_FORBIDDEN
            );
        }

        $this->userService->delete($id, $tenantId);

        return response()->json(
            $this->successResponse('User deleted successfully')
        );
    }

    private function resolveCurrentTenantId(Request $request): ?string
    {
        return app()->bound('currentTenant')
            ? app('currentTenant')?->id
            : $request->header('X-Tenant-ID');
    }
}
