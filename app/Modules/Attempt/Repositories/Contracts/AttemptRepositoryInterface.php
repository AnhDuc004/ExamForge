<?php

namespace App\Modules\Attempt\Repositories\Contracts;

interface AttemptRepositoryInterface
{
    public function findById(string $id);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function countByAssignment(string $assignmentId): int;

    public function findActiveByAssignment(string $assignmentId);

    public function listPendingForGrading(string $tenantId, int $page = 1, int $perPage = 15);

    public function listFinalizedByTest(string $testId, string $tenantId, int $page = 1, int $perPage = 15);
}
