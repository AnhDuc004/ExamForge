<?php

namespace App\Modules\Auth\Services;

use App\Shared\Services\BaseService;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
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

    public function login(LoginDTO $dto, ?string $tenantId = null): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

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

    public function register(RegisterDTO $dto, ?string $tenantId = null): array
    {
        // Check if user already exists
        $existing = $this->userRepository->findByEmail($dto->email);
        if ($existing) {
            throw ValidationException::withMessages([
                'email' => ['Email is already registered.'],
            ]);
        }

        // Use default tenant if no tenant_id provided
        if (!$tenantId) {
            $defaultTenant = $this->tenantRepository->findDefaultTenant();
            $tenantId = $defaultTenant?->id;
        }

        $user = $this->userRepository->create([
            'email' => $dto->email,
            'display_name' => $dto->display_name,
            'password_hash' => Hash::make($dto->password),
            'tenant_id' => $tenantId,
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
}
