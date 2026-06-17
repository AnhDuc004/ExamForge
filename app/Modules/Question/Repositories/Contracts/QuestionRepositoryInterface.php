<?php

namespace App\Modules\Question\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

interface QuestionRepositoryInterface
{
    public function findById(string $id);

    public function findByIdAndTenant(string $id, string $tenantId);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;

    public function updateByTenant(string $id, string $tenantId, array $attributes);

    public function deleteByTenant(string $id, string $tenantId): void;

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): LengthAwarePaginator;

    public function listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): LengthAwarePaginator;

    public function bulkUpdateByTenantAndIds(string $tenantId, array $questionIds, array $attributes): int;
}
