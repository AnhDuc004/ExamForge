<?php

namespace App\Modules\Role\Services;

use App\Modules\Role\DTOs\CreateRoleDTO;
use App\Modules\Role\DTOs\UpdateRoleDTO;
use App\Modules\Role\Models\Role;
use App\Modules\Role\Repositories\Contracts\RoleRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Validation\ValidationException;

class RoleService extends BaseService
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function list(string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->roleRepository->listAccessible($tenantId, $page, $perPage);
    }

    public function find(string $id, string $tenantId): ?Role
    {
        return $this->roleRepository->findAccessibleById($id, $tenantId);
    }

    public function create(CreateRoleDTO $dto, string $tenantId): Role
    {
        $existing = $this->roleRepository->findByTenantAndName($tenantId, $dto->name);
        if ($existing) {
            throw ValidationException::withMessages([
                'name' => ['This role already exists for the selected tenant.'],
            ]);
        }

        $role = $this->roleRepository->create([
            'tenant_id' => $tenantId,
            'name' => $dto->name,
            'description' => $dto->description,
        ]);

        if ($dto->permission_ids !== null) {
            $role->permissions()->sync($dto->permission_ids);
        }

        return $role->load('permissions');
    }

    public function update(string $id, UpdateRoleDTO $dto, string $tenantId): Role
    {
        $role = $this->roleRepository->findById($id);

        if (!$role) {
            throw ValidationException::withMessages([
                'role' => ['Role not found.'],
            ]);
        }

        if ($role->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'role' => ['Only roles in the current tenant can be updated.'],
            ]);
        }

        $name = $dto->name ?? $role->name;

        $existing = $this->roleRepository->findByTenantAndName($tenantId, $name);
        if ($existing && $existing->id !== $role->id) {
            throw ValidationException::withMessages([
                'name' => ['This role already exists for the selected tenant.'],
            ]);
        }

        $attributes = [];

        if ($dto->name !== null) {
            $attributes['name'] = $dto->name;
        }

        if ($dto->description !== null) {
            $attributes['description'] = $dto->description;
        }

        $updated = $this->roleRepository->update($id, $attributes);

        if ($dto->permission_ids !== null && $updated) {
            $updated->permissions()->sync($dto->permission_ids);
        }

        return $updated->load('permissions');
    }

    public function delete(string $id, string $tenantId): void
    {
        $role = $this->roleRepository->findById($id);

        if (!$role) {
            throw ValidationException::withMessages([
                'role' => ['Role not found.'],
            ]);
        }

        if ($role->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'role' => ['Only roles in the current tenant can be deleted.'],
            ]);
        }

        $this->roleRepository->delete($id);
    }
}
