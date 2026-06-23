<?php

namespace App\Modules\Role\Repositories\Contracts;

interface RoleRepositoryInterface
{
    public function findById(string $id);

    public function findAccessibleById(string $id, string $tenantId);

    public function findByTenantAndName(?string $tenantId, string $name);

    public function list(?string $tenantId = null, int $page = 1, int $perPage = 15);

    public function listAccessible(string $tenantId, int $page = 1, int $perPage = 15);

    public function findAssignableByIds(string $tenantId, array $roleIds);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;
}
