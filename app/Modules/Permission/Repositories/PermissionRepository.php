<?php

namespace App\Modules\Permission\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\Permission\Repositories\Contracts\PermissionRepositoryInterface;
use App\Modules\Permission\Models\Permission;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->find($id);
    }

    public function findByResourceAndAction(string $resource, string $action)
    {
        return $this->model
            ->where('resource', $resource)
            ->where('action', $action)
            ->first();
    }

    public function list(int $page = 1, int $perPage = 15)
    {
        return $this->model->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $permission = $this->findById($id);

        if ($permission) {
            $permission->update($attributes);
        }

        return $permission;
    }

    public function delete(string $id): void
    {
        $permission = $this->findById($id);

        if ($permission) {
            $permission->delete();
        }
    }
}
