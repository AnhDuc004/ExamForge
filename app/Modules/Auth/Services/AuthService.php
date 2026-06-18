<?php

namespace App\Modules\Auth\Services;

use App\Shared\Services\BaseService;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\CreateStudentInvitationDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Models\StudentInvitation;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
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

        if ($dto->invitation_token) {
            return $this->registerWithInvitation($dto);
        }

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

    public function createStudentInvitation(CreateStudentInvitationDTO $dto, string $tenantId, $invitedBy): array
    {
        $studentRole = Role::where('name', 'Student')->first();
        if (!$studentRole) {
            throw ValidationException::withMessages([
                'role' => ['Student role not found.'],
            ]);
        }

        $token = bin2hex(random_bytes(32));
        $invitation = StudentInvitation::create([
            'tenant_id' => $tenantId,
            'invited_by' => $invitedBy->id,
            'email' => $dto->email,
            'token' => $token,
            'expires_at' => now()->addDays($dto->expires_in_days ?? 7),
        ]);

        return [
            'invitation' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'token' => $invitation->token,
                'invite_url' => config('app.frontend_url', config('app.url')) . '/register?invite=' . $invitation->token,
                'expires_at' => $invitation->expires_at,
            ],
        ];
    }

    public function getStudentInvitation(string $token): array
    {
        $invitation = StudentInvitation::where('token', $token)->first();
        if (!$invitation) {
            throw ValidationException::withMessages([
                'invitation_token' => ['Invalid invitation token.'],
            ]);
        }

        return [
            'invitation' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'tenant_id' => $invitation->tenant_id,
                'expires_at' => $invitation->expires_at,
                'used_at' => $invitation->used_at,
                'is_expired' => $invitation->expires_at ? now()->greaterThan($invitation->expires_at) : false,
                'is_used' => $invitation->used_at !== null,
            ],
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

    private function registerWithInvitation(RegisterDTO $dto): array
    {
        $invitation = StudentInvitation::where('token', $dto->invitation_token)->first();
        if (!$invitation) {
            throw ValidationException::withMessages([
                'invitation_token' => ['Invalid invitation token.'],
            ]);
        }

        if ($invitation->used_at) {
            throw ValidationException::withMessages([
                'invitation_token' => ['Invitation token has already been used.'],
            ]);
        }

        if ($invitation->expires_at && now()->greaterThan($invitation->expires_at)) {
            throw ValidationException::withMessages([
                'invitation_token' => ['Invitation token has expired.'],
            ]);
        }

        if ($invitation->email && strcasecmp($invitation->email, $dto->email) !== 0) {
            throw ValidationException::withMessages([
                'email' => ['Email does not match the invitation.'],
            ]);
        }

        $tenant = $this->tenantRepository->findById($invitation->tenant_id);
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

        $studentRole = Role::where('name', 'Student')->first();
        if ($studentRole) {
            $user->roles()->syncWithoutDetaching([
                $studentRole->id => [
                    'model_type' => User::class,
                ],
            ]);
        }

        $invitation->update(['used_at' => now()]);

        $token = $user->createToken(
            $dto->device_name ?? 'ExamForge Device',
            ['*'],
            now()->addDays(365)
        )->plainTextToken;

        return [
            'user' => $user->load('roles'),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
