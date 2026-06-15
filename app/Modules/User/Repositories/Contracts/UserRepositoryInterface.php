<?php

namespace App\Modules\User\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findById(string $id);

    public function findByEmail(string $email);

    public function findByTenantAndEmail(string $tenantId, string $email);

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15);

    public function list(int $page = 1, int $perPage = 15);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;
}
