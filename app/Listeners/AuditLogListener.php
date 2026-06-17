<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Events\AttemptSubmitted;
use App\Events\QuestionCreated;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Attempt\Models\Attempt;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Question\Models\Question;

class AuditLogListener
{
    public function handle($event): void
    {
        if ($event instanceof QuestionCreated) {
            $question = Question::find($event->questionId);
            if (!$question) {
                return;
            }

            $this->record($question->tenant_id, $question->created_by, 'question.created', 'question', $question->id);
            return;
        }

        if ($event instanceof AssignmentCreated) {
            $assignment = Assignment::find($event->assignmentId);
            if (!$assignment) {
                return;
            }

            $this->record($assignment->tenant_id, $assignment->assigned_by, 'assignment.created', 'assignment', $assignment->id, [
                'test_id' => $assignment->test_id,
                'assignee_id' => $assignment->assignee_id,
            ]);
            return;
        }

        if ($event instanceof AttemptSubmitted) {
            $attempt = Attempt::with('assignment')->find($event->attemptId);
            if (!$attempt || !$attempt->assignment) {
                return;
            }

            $this->record($attempt->assignment->tenant_id, $attempt->assignee_id, 'attempt.submitted', 'attempt', $attempt->id, [
                'assignment_id' => $attempt->assignment_id,
                'submitted_at' => $attempt->submitted_at,
            ]);
        }
    }

    private function record(string $tenantId, ?string $actorId, string $action, string $resourceType, string $resourceId, ?array $metadata = null): void
    {
        AuditLog::create([
            'tenant_id' => $tenantId,
            'actor_id' => $actorId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'metadata' => $metadata,
        ]);
    }
}
