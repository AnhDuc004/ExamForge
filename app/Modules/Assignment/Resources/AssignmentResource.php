<?php

namespace App\Modules\Assignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'test_id' => $this->test_id,
            'due_date' => $this->due_date,
            'access_token' => $this->access_token,
            'max_attempts' => $this->max_attempts,
            'current_attempts' => $this->current_attempts,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'test' => $this->whenLoaded('test'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
