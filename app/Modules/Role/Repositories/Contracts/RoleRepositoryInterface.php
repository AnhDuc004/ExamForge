<?php

namespace App\Modules\Role\Repositories\Contracts;

interface RoleRepositoryInterface
{
    public function findById(string $id);
}
