<?php

namespace App\Modules\Question\DTOs;

use App\Shared\DTOs\BaseDTO;

class UpdateQuestionDTO extends BaseDTO
{
    public ?string $type = null;
    public ?string $content = null;
    public ?array $options = null;
    public ?array $correct_answer = null;
    public ?int $max_score = null;
    public ?string $difficulty = null;
    public ?array $tags = null;
    public ?string $status = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}
