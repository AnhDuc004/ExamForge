<?php

namespace App\Modules\Assignment\DTOs;

class CreateAssignmentDTO
{
    public function __construct(
        public string $test_id,
        public string $user_id,
        public ?string $due_date = null,
        public int $max_attempts = 1,
    ) {}
}
