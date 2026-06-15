<?php

namespace App\Modules\User\Services;

use App\Modules\User\DTOs\CreateUserDTO;
use App\Modules\User\DTOs\UpdateUserDTO;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function list(?string $tenantId = null, int $page = 1, int $perPage = 15)
    {
        if ($tenantId) {
            return $this->userRepository->listByTenant($tenantId, $page, $perPage);
        }

        return $this->userRepository->list($page, $perPage);
    }

    public function find(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(CreateUserDTO $dto): User
    {
        $existing = $this->userRepository->findByTenantAndEmail($dto->tenant_id, $dto->email);
        if ($existing) {
            throw ValidationException::withMessages([
                'email' => ['This email is already taken for the selected tenant.'],
            ]);
        }

        $user = $this->userRepository->create([
            'email' => $dto->email,
            'display_name' => $dto->display_name,
            'password_hash' => Hash::make($dto->password),
            'tenant_id' => $dto->tenant_id,
            'is_active' => $dto->is_active ?? true,
        ]);

        if ($dto->role_ids !== null) {
            $user->roles()->syncWithPivotValues($dto->role_ids, [
                'model_type' => User::class,
            ]);
        }

        return $user->load('roles');
    }

    public function update(string $id, UpdateUserDTO $dto): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['User not found.'],
            ]);
        }

        $tenantId = $dto->tenant_id ?? $user->tenant_id;
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

        if ($dto->tenant_id !== null) {
            $attributes['tenant_id'] = $dto->tenant_id;
        }

        if ($dto->is_active !== null) {
            $attributes['is_active'] = $dto->is_active;
        }

        $updated = $this->userRepository->update($id, $attributes);

        if ($dto->role_ids !== null && $updated) {
            $updated->roles()->syncWithPivotValues($dto->role_ids, [
                'model_type' => User::class,
            ]);
        }

        return $updated->load('roles');
    }

    public function delete(string $id): void
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['User not found.'],
            ]);
        }

        $this->userRepository->delete($id);
    }
}
