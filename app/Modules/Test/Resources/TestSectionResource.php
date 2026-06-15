<?php

namespace App\Modules\Test\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'test_id' => $this->test_id,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'position' => $this->position,
            'questions' => $this->whenLoaded('questions', function () {
                return TestSectionQuestionResource::collection($this->questions);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
