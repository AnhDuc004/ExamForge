<?php

namespace App\Modules\Assignment\Services;

use App\Modules\Assignment\DTOs\CreateAssignmentDTO;
use App\Modules\Assignment\DTOs\UpdateAssignmentDTO;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Assignment\Repositories\Contracts\AssignmentRepositoryInterface;
use App\Modules\Test\Repositories\Contracts\TestRepositoryInterface;
use Illuminate\Support\Str;

class AssignmentService
{
    public function __construct(
        private AssignmentRepositoryInterface $assignmentRepository,
        private TestRepositoryInterface $testRepository,
    ) {}

    /**
     * Create new assignment
     */
    public function create(CreateAssignmentDTO $dto, string $tenantId): array
    {
        // Verify test exists and is published
        $test = $this->testRepository->findById($dto->test_id);
        if (!$test) {
            throw new \Exception('Test not found');
        }

        if ($test->status !== 'published') {
            throw new \Exception('Test must be published before assigning');
        }

        // Check if assignment already exists for this user-test combo
        $existing = $this->assignmentRepository->findByUserAndTest($dto->user_id, $dto->test_id);
        if ($existing) {
            throw new \Exception('Assignment already exists for this user and test');
        }

        // Generate unique access token
        $accessToken = Str::random(32) . '.' . uniqid();

        $assignment = $this->assignmentRepository->create([
            'tenant_id' => $tenantId,
            'user_id' => $dto->user_id,
            'test_id' => $dto->test_id,
            'due_date' => $dto->due_date,
            'access_token' => hash('sha256', $accessToken),
            'max_attempts' => $dto->max_attempts ?? 1,
            'current_attempts' => 0,
            'status' => 'assigned',
        ]);

        return [
            'assignment' => $assignment,
            'access_token' => $accessToken, // Return unencrypted token only once
            'message' => 'Assignment created successfully',
        ];
    }

    /**
     * Update assignment
     */
    public function update(string $assignmentId, UpdateAssignmentDTO $dto): array
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) {
            throw new \Exception('Assignment not found');
        }

        $updateData = [];

        if ($dto->due_date !== null) {
            $updateData['due_date'] = $dto->due_date;
        }

        if ($dto->max_attempts !== null) {
            $updateData['max_attempts'] = $dto->max_attempts;
        }

        if ($dto->status !== null) {
            $updateData['status'] = $dto->status;
        }

        if (!empty($updateData)) {
            $assignment = $this->assignmentRepository->update($assignmentId, $updateData);
        }

        return [
            'assignment' => $assignment,
            'message' => 'Assignment updated successfully',
        ];
    }

    /**
     * Get assignment by ID
     */
    public function getById(string $assignmentId): array
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if (!$assignment) {
            throw new \Exception('Assignment not found');
        }

        return [
            'assignment' => $assignment,
        ];
    }

    /**
     * List assignments for tenant
     */
    public function listByTenant(string $tenantId, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByTenant($tenantId, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'Assignments retrieved',
        ];
    }

    /**
     * List assignments for user
     */
    public function listByUser(string $userId, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByUser($userId, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'User assignments retrieved',
        ];
    }

    /**
     * List assignments for user in tenant
     */
    public function listByUserAndTenant(string $userId, string $tenantId, int $page = 1, int $perPage = 15): array
    {
        $assignments = $this->assignmentRepository->listByUserAndTenant($userId, $tenantId, $page, $perPage);

        return [
            'assignments' => $assignments,
            'message' => 'User assignments for tenant retrieved',
        ];
    }

    /**
     * Verify access token and check if attempts available
     */
    public function verifyAccessToken(string $accessTokenHash): array
    {
        $assignment = $this->assignmentRepository->findByAccessToken($accessTokenHash);
        if (!$assignment) {
            throw new \Exception('Invalid or expired access token');
        }

        // Check if due date has passed
        if ($assignment->due_date && now()->greaterThan($assignment->due_date)) {
            $this->assignmentRepository->update($assignment->id, ['status' => 'expired']);
            throw new \Exception('Assignment has expired');
        }

        // Check if max attempts exceeded
        if ($assignment->current_attempts >= $assignment->max_attempts) {
            throw new \Exception('Maximum attempts exceeded');
        }

        return [
            'assignment' => $assignment,
            'message' => 'Access token verified',
        ];
    }

    /**
     * Increment attempt count
     */
    public function incrementAttempts(string $assignmentId): void
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);
        if ($assignment) {
            $newAttempts = $assignment->current_attempts + 1;
            $this->assignmentRepository->update($assignmentId, [
                'current_attempts' => $newAttempts,
                'status' => $newAttempts >= $assignment->max_attempts ? 'completed' : 'started',
            ]);
        }
    }

    /**
     * Delete assignment (soft delete)
     */
    public function delete(string $assignmentId): void
    {
        $this->assignmentRepository->delete($assignmentId);
    }
}
