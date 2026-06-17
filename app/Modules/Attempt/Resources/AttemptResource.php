<?php

namespace App\Modules\Attempt\Resources;

use App\Modules\Answer\Resources\AnswerResource;
use App\Modules\Assignment\Resources\AssignmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'assignee_id' => $this->assignee_id,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
            'expires_at' => $this->expires_at,
            'status' => $this->status,
            'auto_score' => $this->auto_score,
            'manual_score' => $this->manual_score,
            'total_score' => $this->total_score,
            'is_passed' => $this->is_passed,
            'is_finalized' => $this->is_finalized,
            'assignment' => new AssignmentResource($this->whenLoaded('assignment')),
            'test' => $this->whenLoaded('assignment', function () {
                if (!$this->assignment->relationLoaded('test') || !$this->assignment->test) {
                    return null;
                }

                return [
                    'id' => $this->assignment->test->id,
                    'title' => $this->assignment->test->title,
                    'description' => $this->assignment->test->description,
                    'duration_seconds' => $this->assignment->test->duration_seconds,
                    'passing_score' => $this->assignment->test->passing_score,
                    'sections' => $this->assignment->test->sections->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'title' => $section->title,
                            'instructions' => $section->instructions,
                            'position' => $section->position,
                            'questions' => $section->questions->map(function ($sectionQuestion) {
                                $snapshot = $sectionQuestion->question_snapshot ?? [];
                                unset($snapshot['correct_answer']);

                                return [
                                    'id' => $sectionQuestion->id,
                                    'question_id' => $sectionQuestion->question_id,
                                    'position' => $sectionQuestion->position,
                                    'score_override' => $sectionQuestion->score_override,
                                    'question_snapshot' => $snapshot,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            }),
            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
