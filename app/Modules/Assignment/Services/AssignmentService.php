<?php

namespace App\Modules\Assignment\Services;

use App\Events\AssignmentCreated;
use App\Modules\Assignment\DTOs\CreateAssignmentDTO;
use App\Modules\Assignment\DTOs\UpdateAssignmentDTO;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Assignment\Repositories\Contracts\AssignmentRepositoryInterface;
use App\Modules\Test\Repositories\Contracts\TestRepositoryInterface;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    public function __construct(
        private AssignmentRepositoryInterface $assignmentRepository,
        private TestRepositoryInterface $testRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function create(CreateAssignmentDTO $dto, string $tenantId): array
    {
        $result = DB::transaction(function () use ($dto, $tenantId) {
            $test = $this->testRepository->findById($dto->test_id);
            if (!$test) {
                throw ValidationException::withMessages([
                    'test_id' => ['Test not found.'],
                ]);
            }

            if ($test->status !== 'published') {
                throw ValidationException::withMessages([
                    'test_id' => ['Test must be published before assigning.'],
                ]);
            }

            $assignee = $this->userRepository->findById($dto->assignee_id);
            if (!$assignee) {
                throw ValidationException::withMessages([
                    'assignee_id' => ['Assignee not found.'],
                ]);
            }

            if ($assignee->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'assignee_id' => ['Assignee must belong to the same tenant.'],
                ]);
            }

            if ($test->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'test_id' => ['Test must belong to the same tenant.'],
                ]);
            }

            $assignedBy = $this->userRepository->findById($dto->assigned_by);
            if (!$assignedBy) {
                throw ValidationException::withMessages([
                    'assigned_by' => ['Assigned-by user not found.'],
                ]);
            }

            if ($assignedBy->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'assigned_by' => ['Assigned-by user must belong to the same tenant.'],
                ]);
            }

            $existing = $this->assignmentRepository->findByAssigneeAndTest($dto->assignee_id, $dto->test_id);
            if ($existing) {
                throw ValidationException::withMessages([
                    'assignment' => ['Assignment already exists for this assignee and test.'],
                ]);
            }

            $accessToken = null;
            if ($dto->access_type === 'token') {
                $accessToken = Str::random(32) . '.' . uniqid();
            }

            $assignment = $this->assignmentRepository->create([
                'tenant_id' => $tenantId,
                'test_id' => $dto->test_id,
                'assignee_id' => $dto->assignee_id,
                'assigned_by' => $dto->assigned_by,
                'due_at' => $dto->due_at,
                'access_type' => $dto->access_type,
                'access_token' => $accessToken ? hash('sha256', $accessToken) : null,
                'max_attempts' => $dto->max_attempts,
                'status' => 'assigned',
            ]);

            AssignmentCreated::dispatch($assignment->id);

            return [
                'assignment' => $this->assignmentRepository->findById($assignment->id),
                'access_token' => $accessToken,
            ];
        });

        return [
            'assignment' => $result['assignment'],
            'access_token' => $result['access_token'],
            'message' => 'Assignment created successfully',
        ];
    }

    public function update(string $assignmentId, UpdateAssignmentDTO $dto): array
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) {
            throw ValidationException::withMessages([
                'assignment' => ['Assignment not found.'],
            ]);
        }

        $attributes = [];
        $accessToken = null;

        if ($dto->due_at !== null) {
            $attributes['due_at'] = $dto->due_at;
        }

        if ($dto->max_attempts !== null) {
            $attributes['max_attempts'] = $dto->max_attempts;
        }

        if ($dto->status !== null) {
            $attributes['status'] = $dto->status;
        }

        if ($dto->access_type !== null) {
            $attributes['access_type'] = $dto->access_type;

            if ($dto->access_type === 'token' && empty($assignment->access_token)) {
                $accessToken = Str::random(32) . '.' . uniqid();
                $attributes['access_token'] = hash('sha256', $accessToken);
            }

            if ($dto->access_type === 'account') {
                $attributes['access_token'] = null;
            }
        }

        if (!empty($attributes)) {
            $assignment = $this->assignmentRepository->update($assignmentId, $attributes);
        }

        return [
            'assignment' => $this->assignmentRepository->findById($assignmentId),
            'access_token' => $accessToken,
            'message' => 'Assignment updated successfully',
        ];
    }

    public function getById(string $assignmentId): array
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) {
            throw ValidationException::withMessages([
                'assignment' => ['Assignment not found.'],
            ]);
        }

        return [
            'assignment' => $assignment,
        ];
    }

    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByTenant($tenantId, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'Assignments retrieved',
        ];
    }

    public function listByAssignee(string $assigneeId, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByAssignee($assigneeId, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'Assignee assignments retrieved',
        ];
    }

    public function listByAssigneeAndTenant(string $assigneeId, string $tenantId, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByAssigneeAndTenant($assigneeId, $tenantId, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'Assignee assignments for tenant retrieved',
        ];
    }

    public function listByAssignedBy(string $assignedById, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByAssignedBy($assignedById, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'Assigned-by assignments retrieved',
        ];
    }

    public function listByAssignedByAndTenant(string $assignedById, string $tenantId, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByAssignedByAndTenant($assignedById, $tenantId, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'Assigned-by assignments for tenant retrieved',
        ];
    }

    public function verifyAccessToken(string $accessTokenHash): array
    {
        $assignment = $this->assignmentRepository->findByAccessToken($accessTokenHash);
        if (!$assignment) {
            throw ValidationException::withMessages([
                'access_token' => ['Invalid or expired access token.'],
            ]);
        }

        if ($assignment->due_at && now()->greaterThan($assignment->due_at)) {
            $this->assignmentRepository->update($assignment->id, ['status' => 'expired']);
            throw ValidationException::withMessages([
                'assignment' => ['Assignment has expired.'],
            ]);
        }

        return [
            'assignment' => $assignment,
            'message' => 'Access token verified',
        ];
    }

    public function delete(string $assignmentId): void
    {
        $this->assignmentRepository->delete($assignmentId);
    }
}
