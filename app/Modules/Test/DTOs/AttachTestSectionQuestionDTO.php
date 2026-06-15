<?php

namespace App\Modules\Test\DTOs;

use App\Shared\DTOs\BaseDTO;

class AttachTestSectionQuestionDTO extends BaseDTO
{
    public string $question_id;
    public int $position;
    public ?int $score_override = null;
}
