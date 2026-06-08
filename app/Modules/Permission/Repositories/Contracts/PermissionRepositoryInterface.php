<?php

namespace App\Modules\Permission\Repositories\Contracts;

interface PermissionRepositoryInterface
{
    public function findById(string $id);
}
