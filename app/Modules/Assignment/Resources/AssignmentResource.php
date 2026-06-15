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
            'test_id' => $this->test_id,
            'assignee_id' => $this->assignee_id,
            'assigned_by' => $this->assigned_by,
            'due_at' => $this->due_at,
            'access_type' => $this->access_type,
            'max_attempts' => $this->max_attempts,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'test' => $this->whenLoaded('test', function () {
                return [
                    'id' => $this->test->id,
                    'title' => $this->test->title,
                    'status' => $this->test->status,
                ];
            }),
            'assignee' => $this->whenLoaded('assignee', function () {
                return [
                    'id' => $this->assignee->id,
                    'email' => $this->assignee->email,
                    'display_name' => $this->assignee->display_name,
                ];
            }),
            'assigned_by_user' => $this->whenLoaded('assignedBy', function () {
                return [
                    'id' => $this->assignedBy->id,
                    'email' => $this->assignedBy->email,
                    'display_name' => $this->assignedBy->display_name,
                ];
            }),
        ];
    }
}
