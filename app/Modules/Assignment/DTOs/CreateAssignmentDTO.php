<?php

namespace App\Modules\Assignment\DTOs;

class CreateAssignmentDTO
{
    public function __construct(
        public string $test_id,
        public ?string $assignee_id,
        public ?array $assignee_ids,
        public string $assigned_by,
        public string $access_type,
        public ?string $due_at = null,
        public int $max_attempts = 1,
    ) {}
}
