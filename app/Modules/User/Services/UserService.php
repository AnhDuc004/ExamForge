<?php

namespace App\Modules\User\Services;

use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\Role\Repositories\Contracts\RoleRepositoryInterface;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function list(string $tenantId, int $page = 1, int $perPage = 15, ?string $search = null)
    {
        return $this->userRepository->listByTenant($tenantId, $page, $perPage, $search);
    }

    public function listSelectableStudents(string $tenantId, int $page = 1, int $perPage = 15, ?string $search = null)
    {
        return $this->userRepository->listSelectableStudents($tenantId, $page, $perPage, $search);
    }

    public function find(string $id, string $tenantId): ?User
    {
        return $this->userRepository->findByIdForTenant($id, $tenantId);
    }

    public function create(CreateUserDTO $dto, string $tenantId): User
    {
        $existing = $this->userRepository->findByTenantAndEmail($tenantId, $dto->email);
        if ($existing) {
            throw ValidationException::withMessages([
                'email' => ['This email is already taken for the selected tenant.'],
            ]);
        }

        $user = $this->userRepository->create([
            'email' => $dto->email,
            'display_name' => $dto->display_name,
            'password_hash' => Hash::make($dto->password),
            'tenant_id' => $tenantId,
            'is_active' => $dto->is_active ?? true,
        ]);

        if ($dto->role_ids !== null) {
            $this->assertAssignableRoles($tenantId, $dto->role_ids);
            $user->roles()->syncWithPivotValues($dto->role_ids, [
                'model_type' => User::class,
            ]);
        }

        return $user->load('roles');
    }

    public function update(string $id, UpdateUserDTO $dto, string $tenantId): User
    {
        $user = $this->userRepository->findByIdForTenant($id, $tenantId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['User not found.'],
            ]);
        }

        $email = $dto->email ?? $user->email;

        $existing = $this->userRepository->findByTenantAndEmail($tenantId, $email);
        if ($existing && $existing->id !== $user->id) {
            throw ValidationException::withMessages([
                'email' => ['This email is already taken for the selected tenant.'],
            ]);
        }

        $attributes = [];

        if ($dto->email !== null) {
            $attributes['email'] = $dto->email;
        }

        if ($dto->display_name !== null) {
            $attributes['display_name'] = $dto->display_name;
        }

        if ($dto->password !== null) {
            $attributes['password_hash'] = Hash::make($dto->password);
        }

        if ($dto->is_active !== null) {
            $attributes['is_active'] = $dto->is_active;
        }

        $updated = $this->userRepository->update($id, $attributes);

        if ($dto->role_ids !== null && $updated) {
            $this->assertAssignableRoles($tenantId, $dto->role_ids);
            $updated->roles()->syncWithPivotValues($dto->role_ids, [
                'model_type' => User::class,
            ]);
        }

        if ($updated && $dto->is_active === false) {
            $updated->tokens()->delete();
        }

        return $updated->load('roles');
    }

    public function updateStatus(string $id, bool $isActive, string $tenantId): User
    {
        $user = $this->userRepository->findByIdForTenant($id, $tenantId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['User not found.'],
            ]);
        }

        $updated = $this->userRepository->update($id, [
            'is_active' => $isActive,
        ]);

        if (!$isActive) {
            $updated->tokens()->delete();
        }

        return $updated->load('roles');
    }

    public function delete(string $id, string $tenantId): void
    {
        $user = $this->userRepository->findByIdForTenant($id, $tenantId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['User not found.'],
            ]);
        }

        $this->userRepository->delete($id);
    }

    private function assertAssignableRoles(string $tenantId, array $roleIds): void
    {
        if (empty($roleIds)) {
            return;
        }

        $assignableCount = $this->roleRepository->findAssignableByIds($tenantId, $roleIds)->count();

        if ($assignableCount !== count(array_unique($roleIds))) {
            throw ValidationException::withMessages([
                'role_ids' => ['Roles must belong to the current tenant or be system-allowed roles.'],
            ]);
        }
    }
}
