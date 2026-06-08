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
}
