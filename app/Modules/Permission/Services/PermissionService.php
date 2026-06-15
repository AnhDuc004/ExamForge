<?php

namespace App\Modules\Permission\Services;

use App\Modules\Permission\DTOs\CreatePermissionDTO;
use App\Modules\Permission\DTOs\UpdatePermissionDTO;
use App\Modules\Permission\Models\Permission;
use App\Modules\Permission\Repositories\Contracts\PermissionRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Validation\ValidationException;

class PermissionService extends BaseService
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
    ) {
    }

    public function list(int $page = 1, int $perPage = 15)
    {
        return $this->permissionRepository->list($page, $perPage);
    }

    public function find(string $id): ?Permission
    {
        return $this->permissionRepository->findById($id);
    }

    public function create(CreatePermissionDTO $dto): Permission
    {
        $existing = $this->permissionRepository->findByResourceAndAction($dto->resource, $dto->action);
        if ($existing) {
            throw ValidationException::withMessages([
                'resource' => ['This permission already exists.'],
            ]);
        }

        return $this->permissionRepository->create([
            'resource' => $dto->resource,
            'action' => $dto->action,
        ]);
    }

    public function update(string $id, UpdatePermissionDTO $dto): Permission
    {
        $permission = $this->permissionRepository->findById($id);

        if (!$permission) {
            throw ValidationException::withMessages([
                'permission' => ['Permission not found.'],
            ]);
        }

        $resource = $dto->resource ?? $permission->resource;
        $action = $dto->action ?? $permission->action;

        $existing = $this->permissionRepository->findByResourceAndAction($resource, $action);
        if ($existing && $existing->id !== $permission->id) {
            throw ValidationException::withMessages([
                'resource' => ['This permission already exists.'],
            ]);
        }

        $attributes = [];

        if ($dto->resource !== null) {
            $attributes['resource'] = $dto->resource;
        }

        if ($dto->action !== null) {
            $attributes['action'] = $dto->action;
        }

        return $this->permissionRepository->update($id, $attributes);
    }

    public function delete(string $id): void
    {
        $permission = $this->permissionRepository->findById($id);

        if (!$permission) {
            throw ValidationException::withMessages([
                'permission' => ['Permission not found.'],
            ]);
        }

        $this->permissionRepository->delete($id);
    }
}
