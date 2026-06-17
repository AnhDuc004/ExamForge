<?php

namespace App\Modules\Test\Repositories\Contracts;

interface TestRepositoryInterface
{
    public function findById(string $id);

    public function findByIdAndTenant(string $id, string $tenantId);

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15);

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;

    public function updateByTenant(string $id, string $tenantId, array $attributes);

    public function deleteByTenant(string $id, string $tenantId): void;
}
