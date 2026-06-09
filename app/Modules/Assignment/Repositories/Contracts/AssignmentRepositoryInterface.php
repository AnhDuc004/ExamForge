<?php

namespace App\Modules\Assignment\Repositories\Contracts;

interface AssignmentRepositoryInterface
{
    public function findById(string $id);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15);

    public function listByUser(string $userId, int $page = 1, int $perPage = 15);

    public function listByUserAndTenant(string $userId, string $tenantId, int $page = 1, int $perPage = 15);

    public function findByAccessToken(string $accessToken);

    public function findByUserAndTest(string $userId, string $testId);
}
