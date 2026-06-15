<?php

namespace App\Modules\Test\Repositories;

use App\Modules\Test\Models\Test;
use App\Modules\Test\Repositories\Contracts\TestRepositoryInterface;
use App\Shared\Repositories\BaseRepository;

class TestRepository extends BaseRepository implements TestRepositoryInterface
{
    public function __construct(Test $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->with(['creator', 'sections.questions.question'])->where('id', $id)->first();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $test = $this->findById($id);
        if ($test) {
            $test->update($attributes);
            return $test;
        }
        return null;
    }

    public function delete(string $id): void
    {
        $test = $this->findById($id);
        if ($test) {
            $test->delete();
        }
    }

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['creator', 'sections.questions.question'])
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['creator', 'sections.questions.question'])
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
