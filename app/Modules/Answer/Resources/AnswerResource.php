<?php

namespace App\Modules\Answer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attempt_id' => $this->attempt_id,
            'test_section_question_id' => $this->test_section_question_id,
            'response' => $this->response,
            'auto_score' => $this->auto_score,
            'manual_score' => $this->manual_score,
            'review_status' => $this->review_status,
            'reviewer_feedback' => $this->reviewer_feedback,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at,
            'attempt' => $this->whenLoaded('attempt', function () {
                return [
                    'id' => $this->attempt->id,
                    'status' => $this->attempt->status,
                    'is_finalized' => $this->attempt->is_finalized,
                ];
            }),
            'question' => $this->whenLoaded('testSectionQuestion', function () {
                return [
                    'id' => $this->testSectionQuestion->id,
                    'question_id' => $this->testSectionQuestion->question_id,
                    'position' => $this->testSectionQuestion->position,
                    'score_override' => $this->testSectionQuestion->score_override,
                    'question_snapshot' => $this->testSectionQuestion->question_snapshot,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
