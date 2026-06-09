<?php

namespace App\Modules\Question\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'created_by' => $this->created_by,
            'type' => $this->type,
            'content' => $this->content,
            'options' => $this->options,
            'correct_answer' => $this->correct_answer,
            'max_score' => $this->max_score,
            'difficulty' => $this->difficulty,
            'tags' => $this->tags,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
