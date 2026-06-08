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
        return $this->model->find($id);
    }
}
