<?php

namespace App\Modules\User\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findById(string $id);

    public function findByIdForTenant(string $id, string $tenantId);

    public function findByEmail(string $email);

    public function findByTenantAndEmail(string $tenantId, string $email);

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15, ?string $search = null);

    public function listSelectableStudents(string $tenantId, int $page = 1, int $perPage = 15, ?string $search = null);

    public function list(int $page = 1, int $perPage = 15, ?string $search = null);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;
}
