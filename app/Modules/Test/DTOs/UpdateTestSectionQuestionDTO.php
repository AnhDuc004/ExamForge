<?php

namespace App\Modules\Test\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdateTestSectionQuestionDTO extends BaseDTO
{
    public ?int $position = null;
    public ?int $score_override = null;
}
