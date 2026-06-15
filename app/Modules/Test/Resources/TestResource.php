<?php

namespace App\Modules\Test\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'description' => $this->description,
            'duration_seconds' => $this->duration_seconds,
            'passing_score' => $this->passing_score,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'email' => $this->creator->email,
                    'display_name' => $this->creator->display_name,
                ];
            }),
            'sections' => $this->whenLoaded('sections', function () {
                return TestSectionResource::collection($this->sections);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
