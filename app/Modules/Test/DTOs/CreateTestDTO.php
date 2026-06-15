<?php

namespace App\Modules\Test\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreateTestDTO extends BaseDTO
{
    public string $title;
    public ?string $description = null;
    public int $duration_seconds;
    public int $passing_score;
    public ?array $sections = null;
}
