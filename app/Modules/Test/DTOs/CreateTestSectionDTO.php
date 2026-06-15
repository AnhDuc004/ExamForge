<?php

namespace App\Modules\Test\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreateTestSectionDTO extends BaseDTO
{
    public string $title;
    public ?string $instructions = null;
    public int $position;
    public ?array $questions = null;
}
