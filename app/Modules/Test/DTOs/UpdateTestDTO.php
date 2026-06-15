<?php

namespace App\Modules\Test\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdateTestDTO extends BaseDTO
{
    public ?string $title = null;
    public ?string $description = null;
    public ?int $duration_seconds = null;
    public ?int $passing_score = null;
}
