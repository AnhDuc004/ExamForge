<?php

namespace App\Modules\Attempt\Repositories;

use App\Modules\Attempt\Models\Attempt;
use App\Modules\Attempt\Repositories\Contracts\AttemptRepositoryInterface;
use App\Shared\Repositories\BaseRepository;

class AttemptRepository extends BaseRepository implements AttemptRepositoryInterface
{
    public function __construct(Attempt $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model
            ->with(['assignment.test.sections.questions', 'answers.testSectionQuestion'])
            ->where('id', $id)
            ->first();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $attempt = $this->model->where('id', $id)->first();
        if ($attempt) {
            $attempt->update($attributes);
        }

        return $attempt;
    }

    public function countByAssignment(string $assignmentId): int
    {
        return $this->model->where('assignment_id', $assignmentId)->count();
    }

    public function findActiveByAssignment(string $assignmentId)
    {
        return $this->model
            ->where('assignment_id', $assignmentId)
            ->where('status', 'in_progress')
            ->first();
    }

    public function listPendingForGrading(string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['assignment.test', 'assignment.assignee', 'answers.testSectionQuestion'])
            ->whereHas('assignment', fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereIn('status', ['submitted'])
            ->where('is_finalized', false)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listFinalizedByTest(string $testId, string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['assignment.test', 'assignment.assignee', 'answers.testSectionQuestion'])
            ->whereHas('assignment', function ($query) use ($testId, $tenantId) {
                $query->where('tenant_id', $tenantId)
                    ->where('test_id', $testId);
            })
            ->where('is_finalized', true)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
