<?php

namespace App\Modules\Question\DTOs;

use App\Shared\DTOs\BaseDTO;

class CreateQuestionDTO extends BaseDTO
{
    public string $type;
    public string $content;
    public ?array $options = null;
    public ?array $correct_answer = null;
    public int $max_score = 1;
    public string $difficulty;
    public ?array $tags = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
