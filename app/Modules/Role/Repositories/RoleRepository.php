<?php

namespace App\Modules\Role\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\Role\Repositories\Contracts\RoleRepositoryInterface;
use App\Modules\Role\Models\Role;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->with('permissions')->find($id);
    }

    public function findByTenantAndName(?string $tenantId, string $name)
    {
        $query = $this->model->with('permissions')->where('name', $name);

        if ($tenantId === null) {
            return $query->whereNull('tenant_id')->first();
        }

        return $query->where('tenant_id', $tenantId)->first();
    }

    public function list(?string $tenantId = null, int $page = 1, int $perPage = 15)
    {
        $query = $this->model->newQuery()->with('permissions');

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $role = $this->findById($id);

        if ($role) {
            $role->update($attributes);
        }

        return $role;
    }

    public function delete(string $id): void
    {
        $role = $this->findById($id);

        if ($role) {
            $role->delete();
        }
    }
}
