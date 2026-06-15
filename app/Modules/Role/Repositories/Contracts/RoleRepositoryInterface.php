<?php

namespace App\Modules\Role\Repositories\Contracts;

interface RoleRepositoryInterface
{
    public function findById(string $id);

    public function findByTenantAndName(?string $tenantId, string $name);

    public function list(?string $tenantId = null, int $page = 1, int $perPage = 15);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;
}
