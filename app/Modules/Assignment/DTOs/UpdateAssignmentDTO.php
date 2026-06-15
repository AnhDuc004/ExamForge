<?php

namespace App\Modules\Assignment\DTOs;

class UpdateAssignmentDTO
{
    public function __construct(
        public ?string $due_at = null,
        public ?int $max_attempts = null,
        public ?string $status = null,
        public ?string $access_type = null,
    ) {}
}
