<?php

namespace App\Modules\Grading\Services;

use App\Modules\Answer\Repositories\Contracts\AnswerRepositoryInterface;
use App\Modules\Assignment\Repositories\Contracts\AssignmentRepositoryInterface;
use App\Modules\Attempt\Repositories\Contracts\AttemptRepositoryInterface;
use App\Modules\Audit\Repositories\Contracts\AuditRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradingService extends BaseService
{
    public function __construct(
        private AttemptRepositoryInterface $attemptRepository,
        private AnswerRepositoryInterface $answerRepository,
        private AssignmentRepositoryInterface $assignmentRepository,
        private AuditRepositoryInterface $auditRepository,
    ) {
    }

    public function pending(string $tenantId, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return $this->attemptRepository->listPendingForGrading($tenantId, $page, $perPage);
    }

    public function reviewAnswer(
        string $answerId,
        string $tenantId,
        string $reviewerId,
        int $manualScore,
        ?string $feedback
    ): array {
        $answer = DB::transaction(function () use ($answerId, $tenantId, $reviewerId, $manualScore, $feedback) {
            $answer = $this->answerRepository->findById($answerId);

            if (!$answer || $answer->attempt->assignment->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'answer' => ['Answer not found.'],
                ]);
            }

            if ($answer->attempt->is_finalized) {
                throw ValidationException::withMessages([
                    'attempt' => ['Finalized attempts cannot be reviewed.'],
                ]);
            }

            $maxScore = $this->maxScoreForAnswer($answer);
            if ($manualScore > $maxScore) {
                throw ValidationException::withMessages([
                    'manual_score' => ["Manual score cannot exceed {$maxScore}."],
                ]);
            }

            $updated = $this->answerRepository->update($answerId, [
                'manual_score' => $manualScore,
                'review_status' => 'reviewed',
                'reviewer_feedback' => $feedback,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);

            $this->auditRepository->record([
                'tenant_id' => $tenantId,
                'actor_id' => $reviewerId,
                'action' => 'answer.reviewed',
                'resource_type' => 'answer',
                'resource_id' => $answerId,
                'metadata' => [
                    'attempt_id' => $answer->attempt_id,
                    'manual_score' => $manualScore,
                ],
            ]);

            return $updated;
        });

        return [
            'answer' => $answer,
            'message' => 'Answer reviewed successfully',
        ];
    }

    public function finalizeAttempt(string $attemptId, string $tenantId, string $reviewerId): array
    {
        $attempt = DB::transaction(function () use ($attemptId, $tenantId, $reviewerId) {
            $attempt = $this->attemptRepository->findById($attemptId);

            if (!$attempt || $attempt->assignment->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'attempt' => ['Attempt not found.'],
                ]);
            }

            if (!in_array($attempt->status, ['submitted'], true)) {
                throw ValidationException::withMessages([
                    'attempt' => ['Only submitted attempts can be finalized.'],
                ]);
            }

            if ($attempt->is_finalized) {
                throw ValidationException::withMessages([
                    'attempt' => ['Attempt is already finalized.'],
                ]);
            }

            [$autoScore, $manualScore] = $this->scoreAttempt($attempt);
            $totalScore = $autoScore + $manualScore;
            $isPassed = $totalScore >= $attempt->assignment->test->passing_score;

            $this->attemptRepository->update($attempt->id, [
                'auto_score' => $autoScore,
                'manual_score' => $manualScore,
                'total_score' => $totalScore,
                'is_passed' => $isPassed,
                'is_finalized' => true,
                'status' => 'finalized',
            ]);

            $this->assignmentRepository->update($attempt->assignment_id, ['status' => 'completed']);

            $this->auditRepository->record([
                'tenant_id' => $tenantId,
                'actor_id' => $reviewerId,
                'action' => 'attempt.finalized',
                'resource_type' => 'attempt',
                'resource_id' => $attempt->id,
                'metadata' => [
                    'auto_score' => $autoScore,
                    'manual_score' => $manualScore,
                    'total_score' => $totalScore,
                    'is_passed' => $isPassed,
                ],
            ]);

            return $this->attemptRepository->findById($attempt->id);
        });

        return [
            'attempt' => $attempt,
            'message' => 'Attempt finalized successfully',
        ];
    }

    private function scoreAttempt($attempt): array
    {
        $autoScore = 0;
        $manualScore = 0;

        foreach ($attempt->answers as $answer) {
            $snapshot = $answer->testSectionQuestion->question_snapshot ?? [];
            $type = $snapshot['type'] ?? null;

            if ($this->isObjective($type)) {
                $score = $this->scoreObjectiveAnswer($answer);
                $this->answerRepository->update($answer->id, [
                    'auto_score' => $score,
                    'review_status' => 'auto_graded',
                ]);
                $autoScore += $score;
                continue;
            }

            if ($answer->manual_score === null) {
                throw ValidationException::withMessages([
                    'answers' => ['All subjective answers must be reviewed before finalizing.'],
                ]);
            }

            $manualScore += $answer->manual_score;
        }

        return [$autoScore, $manualScore];
    }

    private function scoreObjectiveAnswer($answer): int
    {
        $snapshot = $answer->testSectionQuestion->question_snapshot ?? [];
        $correct = $snapshot['correct_answer'] ?? null;
        $response = $answer->response['answer'] ?? $answer->response ?? null;

        if ($correct === null || $response === null) {
            return 0;
        }

        $isCorrect = is_array($correct)
            ? $this->normalizeArray($correct) === $this->normalizeArray((array) $response)
            : trim((string) $correct) === trim((string) $response);

        return $isCorrect ? $this->maxScoreForAnswer($answer) : 0;
    }

    private function isObjective(?string $type): bool
    {
        return in_array($type, ['multiple_choice', 'true_false', 'short_answer'], true);
    }

    private function maxScoreForAnswer($answer): int
    {
        $snapshot = $answer->testSectionQuestion->question_snapshot ?? [];

        return (int) ($answer->testSectionQuestion->score_override ?? $snapshot['max_score'] ?? 0);
    }

    private function normalizeArray(array $value): array
    {
        $normalized = array_map(fn ($item) => trim((string) $item), $value);
        sort($normalized);

        return $normalized;
    }
}
