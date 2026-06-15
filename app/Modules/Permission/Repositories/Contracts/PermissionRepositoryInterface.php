<?php

namespace App\Modules\Permission\Repositories\Contracts;

interface PermissionRepositoryInterface
{
    public function findById(string $id);

    public function findByResourceAndAction(string $resource, string $action);

    public function list(int $page = 1, int $perPage = 15);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;
}
