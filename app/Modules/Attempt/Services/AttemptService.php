<?php

namespace App\Modules\Attempt\Services;

use App\Events\AttemptSubmitted;
use App\Modules\Answer\Repositories\Contracts\AnswerRepositoryInterface;
use App\Modules\Assignment\Repositories\Contracts\AssignmentRepositoryInterface;
use App\Modules\Attempt\Repositories\Contracts\AttemptRepositoryInterface;
use App\Modules\Audit\Models\AuditLog;
use App\Shared\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttemptService extends BaseService
{
    public function __construct(
        private AttemptRepositoryInterface $attemptRepository,
        private AssignmentRepositoryInterface $assignmentRepository,
        private AnswerRepositoryInterface $answerRepository,
    ) {
    }

    public function start(string $assignmentId, string $assigneeId, string $tenantId): array
    {
        $attempt = DB::transaction(function () use ($assignmentId, $assigneeId, $tenantId) {
            $assignment = $this->resolveAssignmentForAssignee($assignmentId, $assigneeId, $tenantId);

            if ($assignment->due_at && now()->greaterThan($assignment->due_at)) {
                $this->assignmentRepository->update($assignment->id, ['status' => 'expired']);
                throw ValidationException::withMessages([
                    'assignment' => ['Assignment has expired.'],
                ]);
            }

            $activeAttempt = $this->attemptRepository->findActiveByAssignment($assignment->id);
            if ($activeAttempt) {
                return $activeAttempt;
            }

            if ($this->attemptRepository->countByAssignment($assignment->id) >= $assignment->max_attempts) {
                throw ValidationException::withMessages([
                    'assignment' => ['Maximum attempts reached.'],
                ]);
            }

            $expiresAt = now()->addSeconds($assignment->test->duration_seconds);
            if ($assignment->due_at && $assignment->due_at->lessThan($expiresAt)) {
                $expiresAt = $assignment->due_at;
            }

            $attempt = $this->attemptRepository->create([
                'assignment_id' => $assignment->id,
                'assignee_id' => $assigneeId,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'status' => 'in_progress',
            ]);

            $this->assignmentRepository->update($assignment->id, ['status' => 'started']);

            return $attempt;
        });

        return [
            'attempt' => $this->attemptRepository->findById($attempt->id),
            'message' => 'Attempt started successfully',
        ];
    }

    public function show(string $attemptId, string $assigneeId, string $tenantId): array
    {
        $attempt = $this->resolveAttemptForAssignee($attemptId, $assigneeId, $tenantId);
        $this->expireAttemptIfNeeded($attempt);

        return [
            'attempt' => $this->attemptRepository->findById($attemptId),
            'message' => 'Attempt retrieved',
        ];
    }

    public function heartbeat(string $attemptId, string $assigneeId, string $tenantId): array
    {
        $attempt = $this->resolveAttemptForAssignee($attemptId, $assigneeId, $tenantId);
        $this->ensureAttemptCanBeEdited($attempt);

        $attempt->touch();

        return [
            'attempt' => $this->attemptRepository->findById($attemptId),
            'message' => 'Attempt heartbeat received',
            'server_time' => now()->toIso8601String(),
            'remaining_seconds' => $attempt->expires_at
                ? now()->diffInSeconds($attempt->expires_at)
                : null,
        ];
    }

    public function resume(string $attemptId, string $assigneeId, string $tenantId): array
    {
        $attempt = $this->resolveAttemptForAssignee($attemptId, $assigneeId, $tenantId);
        $this->expireAttemptIfNeeded($attempt);

        if (!in_array($attempt->status, ['in_progress', 'expired'], true)) {
            throw ValidationException::withMessages([
                'attempt' => ['Attempt cannot be resumed.'],
            ]);
        }

        $attempt->touch();

        return [
            'attempt' => $this->attemptRepository->findById($attemptId),
            'message' => 'Attempt resumed successfully',
            'server_time' => now()->toIso8601String(),
        ];
    }

    public function saveAnswer(string $attemptId, string $assigneeId, string $tenantId, string $testSectionQuestionId, ?array $response): array
    {
        $answer = DB::transaction(function () use ($attemptId, $assigneeId, $tenantId, $testSectionQuestionId, $response) {
            $attempt = $this->resolveAttemptForAssignee($attemptId, $assigneeId, $tenantId);
            $this->ensureAttemptCanBeEdited($attempt);

            $validQuestion = $attempt->assignment->test->sections
                ->flatMap(fn ($section) => $section->questions)
                ->contains(fn ($question) => $question->id === $testSectionQuestionId);

            if (!$validQuestion) {
                throw ValidationException::withMessages([
                    'test_section_question_id' => ['Question does not belong to this attempt.'],
                ]);
            }

            return $this->answerRepository->upsertForAttempt($attempt->id, $testSectionQuestionId, [
                'response' => $response,
                'review_status' => 'pending',
            ]);
        });

        return [
            'answer' => $answer,
            'message' => 'Answer saved',
        ];
    }

    public function submit(string $attemptId, string $assigneeId, string $tenantId): array
    {
        $attempt = DB::transaction(function () use ($attemptId, $assigneeId, $tenantId) {
            $attempt = $this->resolveAttemptForAssignee($attemptId, $assigneeId, $tenantId);

            if (!in_array($attempt->status, ['in_progress', 'expired'], true)) {
                throw ValidationException::withMessages([
                    'attempt' => ['Attempt cannot be submitted.'],
                ]);
            }

            $submittedAt = now();
            if ($attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
                $submittedAt = $attempt->expires_at;
            }

            $this->attemptRepository->update($attempt->id, [
                'status' => 'submitted',
                'submitted_at' => $submittedAt,
            ]);

            $this->assignmentRepository->update($attempt->assignment_id, ['status' => 'completed']);

            AttemptSubmitted::dispatch($attempt->id);

            return $this->attemptRepository->findById($attempt->id);
        });

        return [
            'attempt' => $attempt,
            'message' => 'Attempt submitted successfully',
        ];
    }

    public function forceSubmit(string $attemptId, string $tenantId, ?string $actorId = null): array
    {
        $attempt = DB::transaction(function () use ($attemptId, $tenantId, $actorId) {
            $attempt = $this->resolveAttemptForTenant($attemptId, $tenantId);

            if (in_array($attempt->status, ['submitted', 'finalized'], true)) {
                throw ValidationException::withMessages([
                    'attempt' => ['Attempt has already been submitted.'],
                ]);
            }

            $submittedAt = now();
            if ($attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
                $submittedAt = $attempt->expires_at;
            }

            $this->attemptRepository->update($attempt->id, [
                'status' => 'submitted',
                'submitted_at' => $submittedAt,
            ]);

            $this->assignmentRepository->update($attempt->assignment_id, ['status' => 'completed']);

            AttemptSubmitted::dispatch($attempt->id);

            AuditLog::create([
                'tenant_id' => $tenantId,
                'actor_id' => $actorId,
                'action' => 'attempt.force_submitted',
                'resource_type' => 'attempt',
                'resource_id' => $attempt->id,
                'metadata' => array_filter([
                    'assignment_id' => $attempt->assignment_id,
                    'ip' => request()?->ip(),
                ], fn ($value) => $value !== null),
            ]);

            return $this->attemptRepository->findById($attempt->id);
        });

        return [
            'attempt' => $attempt,
            'message' => 'Attempt force submitted successfully',
        ];
    }

    private function resolveAssignmentForAssignee(string $assignmentId, string $assigneeId, string $tenantId)
    {
        $assignment = $this->assignmentRepository->findById($assignmentId);

        if (!$assignment || $assignment->tenant_id !== $tenantId || $assignment->assignee_id !== $assigneeId) {
            throw ValidationException::withMessages([
                'assignment' => ['Assignment not found.'],
            ]);
        }

        if (!in_array($assignment->status, ['assigned', 'started'], true)) {
            throw ValidationException::withMessages([
                'assignment' => ['Assignment is not available.'],
            ]);
        }

        return $assignment;
    }

    private function resolveAttemptForAssignee(string $attemptId, string $assigneeId, string $tenantId)
    {
        $attempt = $this->attemptRepository->findById($attemptId);

        if (!$attempt || $attempt->assignee_id !== $assigneeId || $attempt->assignment->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'attempt' => ['Attempt not found.'],
            ]);
        }

        return $attempt;
    }

    private function resolveAttemptForTenant(string $attemptId, string $tenantId)
    {
        $attempt = $this->attemptRepository->findById($attemptId);

        if (!$attempt || $attempt->assignment->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'attempt' => ['Attempt not found.'],
            ]);
        }

        return $attempt;
    }

    private function ensureAttemptCanBeEdited($attempt): void
    {
        $this->expireAttemptIfNeeded($attempt);

        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'attempt' => ['Attempt is not editable.'],
            ]);
        }
    }

    private function expireAttemptIfNeeded($attempt): void
    {
        if ($attempt->status === 'in_progress' && $attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
            $this->attemptRepository->update($attempt->id, ['status' => 'expired']);
            $this->assignmentRepository->update($attempt->assignment_id, ['status' => 'expired']);
            $attempt->status = 'expired';
        }
    }
}
