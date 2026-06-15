<?php

namespace App\Modules\Question\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\Question\Repositories\Contracts\QuestionRepositoryInterface;
use App\Modules\Question\Models\Question;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function __construct(Question $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->find($id);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $question = $this->model->find($id);
        if ($question) {
            $question->update($attributes);
        }

        return $question;
    }

    public function delete(string $id): void
    {
        $this->model->destroy($id);
    }

    public function listByTenant(
        string $tenantId,
        int $page = 1,
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('status', $status)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->whereJsonContains('tags', $tags)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
