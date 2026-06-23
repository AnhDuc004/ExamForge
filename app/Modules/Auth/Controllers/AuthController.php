<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\DTOs\CreateStudentInvitationDTO;
use App\Modules\Auth\Requests\CreateStudentInvitationRequest;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\UserResource;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Resources\TenantResource;
use App\Modules\Auth\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $tenantIdentifier = $this->resolveTenantIdentifier($request);

        $dto = new LoginDTO([
            'email' => $request->email,
            'password' => $request->password,
            'device_name' => $request->device_name,
        ]);

        $result = $this->authService->login($dto, $tenantIdentifier, $tenantIdentifier !== null);

        return response()->json(
            $this->successResponse(
                'Login successful',
                [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => $result['token_type'],
                ]
            )
        );
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $tenantIdentifier = $this->resolveTenantIdentifier($request);

        $dto = new RegisterDTO([
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password' => $request->password,
            'device_name' => $request->device_name,
            'tenant_id' => null,
            'invitation_token' => $request->invitation_token,
        ]);

        $result = $this->authService->register($dto, $tenantIdentifier, $tenantIdentifier !== null);

        return response()->json(
            $this->successResponse(
                'Registration successful',
                [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => $result['token_type'],
                ]
            ),
            201
        );
    }

    public function createStudentInvitation(CreateStudentInvitationRequest $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? app('currentTenant')?->id;

        if (!$tenantId) {
            return response()->json($this->errorResponse('Tenant not resolved'), 400);
        }

        try {
            $dto = new CreateStudentInvitationDTO([
                'email' => $request->email,
                'expires_in_days' => $request->expires_in_days ?? 7,
            ]);

            $result = $this->authService->createStudentInvitation($dto, $tenantId, $request->user());

            return response()->json(
                $this->successResponse('Student invitation created', $result),
                201
            );
        } catch (\Throwable $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function showStudentInvitation(string $token): JsonResponse
    {
        try {
            $result = $this->authService->getStudentInvitation($token);

            return response()->json(
                $this->successResponse('Invitation retrieved', $result)
            );
        } catch (\Throwable $e) {
            return response()->json(
                $this->errorResponse($e->getMessage()),
                400
            );
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(
            $this->successResponse('Logout successful')
        );
    }

    public function revokeAllTokens(Request $request): JsonResponse
    {
        $this->authService->revokeAllTokens($request->user());

        return response()->json(
            $this->successResponse('All tokens revoked')
        );
    }

    public function refresh(Request $request): JsonResponse
    {
        \Log::info('Refreshing token for user', ['user_id' => $request->user()->id, 'device_name' => $request->input('device_name')]);
        $result = $this->authService->refreshToken(
            $request->user(),
            $request->input('device_name')
        );

        return response()->json(
            $this->successResponse(
                'Token refreshed',
                [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => $result['token_type'],
                ]
            )
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles.permissions');
        $tenant = app()->bound('currentTenant')
            ? app('currentTenant')
            : ($user->tenant_id ? Tenant::find($user->tenant_id) : null);

        $permissions = $user->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->unique(fn ($permission) => $permission->resource . ':' . $permission->action)
            ->values()
            ->map(fn ($permission) => [
                'resource' => $permission->resource,
                'action' => $permission->action,
            ]);
        $roles = $user->roles
            ->values()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'tenant_id' => $role->tenant_id,
            ]);

        return response()->json(
            $this->successResponse(
                'User profile',
                [
                    'user' => new UserResource($user),
                    'roles' => $roles,
                    'tenant_id' => $tenant?->id ?? $user->tenant_id,
                    'tenant' => $tenant ? new TenantResource($tenant) : null,
                    'permissions' => $permissions,
                ]
            )
        );
    }

    private function resolveTenantIdentifier(Request $request): ?string
    {
        $tenantId = $request->header('X-Tenant-ID');
        if ($tenantId) {
            return $tenantId;
        }

        $host = $request->getHost();
        if (filter_var($host, FILTER_VALIDATE_IP) || in_array($host, ['localhost', '127.0.0.1'], true)) {
            return null;
        }

        $parts = explode('.', $host);
        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }
}
