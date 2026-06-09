<?php

namespace App\Modules\Question\Repositories\Contracts;

use Illuminate\Pagination\Paginator;

interface QuestionRepositoryInterface
{
    public function findById(string $id);

    public function create(array $attributes);

    public function update(string $id, array $attributes);

    public function delete(string $id): void;

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15): Paginator;

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): Paginator;

    public function listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): Paginator;
}
