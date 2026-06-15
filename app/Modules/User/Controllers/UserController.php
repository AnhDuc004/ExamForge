<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\User\Requests\CreateUserRequest;
use App\Modules\User\Requests\UpdateUserRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private UserService $userService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->query('tenant_id');
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 15);

        $users = $this->userService->list($tenantId, $page, $perPage);

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

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->find($id);

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
        $dto = new CreateUserDTO([
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password' => $request->password,
            'tenant_id' => $request->tenant_id,
            'is_active' => $request->boolean('is_active', true),
            'role_ids' => $request->role_ids,
        ]);

        $user = $this->userService->create($dto);

        return response()->json(
            $this->successResponse('User created successfully', new UserResource($user)),
            201
        );
    }

    public function update(string $id, UpdateUserRequest $request): JsonResponse
    {
        $dto = new UpdateUserDTO([
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password' => $request->password,
            'tenant_id' => $request->tenant_id,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : null,
            'role_ids' => $request->role_ids,
        ]);

        $user = $this->userService->update($id, $dto);

        return response()->json(
            $this->successResponse('User updated successfully', new UserResource($user))
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $this->userService->delete($id);

        return response()->json(
            $this->successResponse('User deleted successfully')
        );
    }
}
