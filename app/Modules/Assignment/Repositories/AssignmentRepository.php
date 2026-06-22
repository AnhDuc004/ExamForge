<?php

namespace App\Modules\Assignment\Repositories;

use App\Modules\Assignment\Models\Assignment;
use App\Modules\Assignment\Repositories\Contracts\AssignmentRepositoryInterface;
use App\Shared\Repositories\BaseRepository;

class AssignmentRepository extends BaseRepository implements AssignmentRepositoryInterface
{
    public function __construct(Assignment $model)
    {
        parent::__construct($model);
    }

    public function findById(string $id)
    {
        return $this->model->with(['test', 'assignee', 'assignedBy', 'tenant'])->where('id', $id)->first();
    }

    public function findByIdForTenant(string $id, string $tenantId)
    {
        return $this->model
            ->with(['test', 'assignee', 'assignedBy', 'tenant'])
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(string $id, array $attributes)
    {
        $assignment = $this->findById($id);
        if ($assignment) {
            $assignment->update($attributes);
            return $assignment;
        }
        return null;
    }

    public function delete(string $id): void
    {
        $assignment = $this->findById($id);
        if ($assignment) {
            $assignment->delete();
        }
    }

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['test', 'assignee', 'assignedBy', 'tenant'])
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByAssignee(string $assigneeId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['test', 'assignee', 'assignedBy', 'tenant'])
            ->where('assignee_id', $assigneeId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByAssigneeAndTenant(string $assigneeId, string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['test', 'assignee', 'assignedBy', 'tenant'])
            ->where('assignee_id', $assigneeId)
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByAssignedBy(string $assignedById, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['test', 'assignee', 'assignedBy', 'tenant'])
            ->where('assigned_by', $assignedById)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByAssignedByAndTenant(string $assignedById, string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->with(['test', 'assignee', 'assignedBy', 'tenant'])
            ->where('assigned_by', $assignedById)
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findByAccessToken(string $accessToken)
    {
        return $this->model
            ->where('access_token', $accessToken)
            ->where('status', '!=', 'expired')
            ->where('status', '!=', 'archived')
            ->where('access_type', 'token')
            ->first();
    }

    public function findByAccessTokenForTenant(string $accessToken, string $tenantId)
    {
        return $this->model
            ->with(['test', 'assignee', 'assignedBy', 'tenant'])
            ->where('access_token', $accessToken)
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', 'expired')
            ->where('status', '!=', 'archived')
            ->where('access_type', 'token')
            ->first();
    }

    public function findByAssigneeAndTest(string $assigneeId, string $testId)
    {
        return $this->model
            ->where('assignee_id', $assigneeId)
            ->where('test_id', $testId)
            ->first();
    }
}
