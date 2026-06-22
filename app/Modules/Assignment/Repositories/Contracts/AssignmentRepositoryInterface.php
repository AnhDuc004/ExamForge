<?php

namespace App\Modules\Assignment\Repositories\Contracts;

interface AssignmentRepositoryInterface
{
    public function findById(string $id);

    public function findByIdForTenant(string $id, string $tenantId);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15);

    public function listByAssignee(string $assigneeId, int $page = 1, int $perPage = 15);

    public function listByAssigneeAndTenant(string $assigneeId, string $tenantId, int $page = 1, int $perPage = 15);

    public function listByAssignedBy(string $assignedById, int $page = 1, int $perPage = 15);

    public function listByAssignedByAndTenant(string $assignedById, string $tenantId, int $page = 1, int $perPage = 15);

    public function findByAccessToken(string $accessToken);

    public function findByAccessTokenForTenant(string $accessToken, string $tenantId);

    public function findByAssigneeAndTest(string $assigneeId, string $testId);
}
