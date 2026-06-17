<?php

namespace App\Modules\Question\Repositories;

use App\Shared\Repositories\BaseRepository;
use App\Modules\Question\Repositories\Contracts\QuestionRepositoryInterface;
use App\Modules\Question\Models\Question;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function findByIdAndTenant(string $id, string $tenantId)
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();
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

    public function updateByTenant(string $id, string $tenantId, array $attributes)
    {
        $question = $this->model
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        if ($question) {
            $question->update($attributes);
        }

        return $question;
    }

    public function deleteByTenant(string $id, string $tenantId): void
    {
        $question = $this->model
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        if ($question) {
            $question->delete();
        }
    }

    public function listByTenant(
        string $tenantId,
        int $page = 1,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = $this->model->where('tenant_id', $tenantId);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('content', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            foreach ($filters['tags'] as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByStatus(string $tenantId, string $status, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listByTenant($tenantId, $page, $perPage, [
            'status' => $status,
        ]);
    }

    public function listByTags(string $tenantId, array $tags, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->listByTenant($tenantId, $page, $perPage, [
            'tags' => $tags,
        ]);
    }

    public function bulkUpdateByTenantAndIds(string $tenantId, array $questionIds, array $attributes): int
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $questionIds)
            ->update($attributes);
    }
}
