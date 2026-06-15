<?php

namespace App\Modules\Test\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestSectionQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'question_id' => $this->question_id,
            'position' => $this->position,
            'score_override' => $this->score_override,
            'question_snapshot' => $this->question_snapshot,
            'question' => $this->whenLoaded('question', function () {
                return [
                    'id' => $this->question->id,
                    'type' => $this->question->type,
                    'content' => $this->question->content,
                    'options' => $this->question->options,
                    'correct_answer' => $this->question->correct_answer,
                    'max_score' => $this->question->max_score,
                    'difficulty' => $this->question->difficulty,
                    'tags' => $this->question->tags,
                    'status' => $this->question->status,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
