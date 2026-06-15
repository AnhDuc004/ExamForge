<?php

namespace App\Modules\Test\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdateTestSectionDTO extends BaseDTO
{
    public ?string $title = null;
    public ?string $instructions = null;
    public ?int $position = null;
}
