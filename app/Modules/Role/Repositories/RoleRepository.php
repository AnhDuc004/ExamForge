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

    public function findAccessibleById(string $id, string $tenantId)
    {
        return $this->model->with('permissions')
            ->where('id', $id)
            ->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)
                    ->orWhereNull('tenant_id');
            })
            ->first();
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

    public function listAccessible(string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model->newQuery()
            ->with('permissions')
            ->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)
                    ->orWhereNull('tenant_id');
            })
            ->orderByRaw('CASE WHEN tenant_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findAssignableByIds(string $tenantId, array $roleIds)
    {
        return $this->model->newQuery()
            ->whereIn('id', $roleIds)
            ->where(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)
                    ->orWhereNull('tenant_id');
            })
            ->get();
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
