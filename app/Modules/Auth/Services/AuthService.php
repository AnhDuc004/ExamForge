<?php

namespace App\Modules\Auth\Services;

use App\Shared\Services\BaseService;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use App\Modules\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TenantRepositoryInterface $tenantRepository,
    ) {
    }

    public function login(LoginDTO $dto, ?string $tenantId = null, bool $strictTenantResolution = false): array
    {
        $tenant = $this->resolveTenant($tenantId, $strictTenantResolution);
        $user = $tenant
            ? $this->userRepository->findByTenantAndEmail($tenant->id, $dto->email)
            : $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This user account is inactive.'],
            ]);
        }

        $token = $user->createToken(
            $dto->device_name ?? 'ExamForge Device',
            ['*'],
            now()->addDays(365)
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function register(RegisterDTO $dto, ?string $tenantId = null, bool $strictTenantResolution = false): array
    {
        $tenant = $this->resolveTenant($tenantId, $strictTenantResolution);

        if (!$tenant) {
            $tenant = $this->tenantRepository->findDefaultTenant();
        }

        if (!$tenant) {
            throw ValidationException::withMessages([
                'tenant' => ['Tenant not found.'],
            ]);
        }

        $existing = $this->userRepository->findByTenantAndEmail($tenant->id, $dto->email);
        if ($existing) {
            throw ValidationException::withMessages([
                'email' => ['Email is already registered.'],
            ]);
        }

        $user = $this->userRepository->create([
            'email' => $dto->email,
            'display_name' => $dto->display_name,
            'password_hash' => Hash::make($dto->password),
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $token = $user->createToken(
            $dto->device_name ?? 'ExamForge Device',
            ['*'],
            now()->addDays(365)
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout($user): void
    {
        // Revoke current token
        $user->currentAccessToken()->delete();
    }

    public function revokeAllTokens($user): void
    {
        // Revoke all tokens for user
        $user->tokens()->delete();
    }

    public function refreshToken($user, ?string $deviceName = null): array
    {
        // Revoke current token and create new one
        $user->currentAccessToken()->delete();

        $token = $user->createToken(
            $deviceName ?? 'ExamForge Device',
            ['*'],
            now()->addDays(365)
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    private function resolveTenant(?string $tenantIdentifier, bool $strict = false): ?Tenant
    {
        if (!$tenantIdentifier) {
            return null;
        }

        $tenant = $this->tenantRepository->findById($tenantIdentifier);
        if ($tenant) {
            return $tenant;
        }

        $tenant = $this->tenantRepository->findBySlug($tenantIdentifier);
        if ($tenant) {
            return $tenant;
        }

        if ($strict) {
            throw ValidationException::withMessages([
                'tenant' => ['Tenant not found.'],
            ]);
        }

        return null;
    }
}
