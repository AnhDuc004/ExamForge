<?php

namespace App\Modules\Assignment\Repositories;

use App\Modules\Assignment\Models\Assignment;
use App\Modules\Assignment\Repositories\Contracts\AssignmentRepositoryInterface;
use App\Shared\Repositories\BaseRepository;

class AssignmentRepository extends BaseRepository implements AssignmentRepositoryInterface
{
    public function __construct()
    {
        $this->model = new Assignment();
    }

    public function findById(string $id)
    {
        return $this->model->where('id', $id)->first();
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
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByUser(string $userId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->where('user_id', $userId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function listByUserAndTenant(string $userId, string $tenantId, int $page = 1, int $perPage = 15)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findByAccessToken(string $accessToken)
    {
        return $this->model
            ->where('access_token', $accessToken)
            ->where('status', '!=', 'expired')
            ->where('status', '!=', 'archived')
            ->first();
    }

    public function findByUserAndTest(string $userId, string $testId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('test_id', $testId)
            ->first();
    }
}
