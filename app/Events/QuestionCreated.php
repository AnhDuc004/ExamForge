<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionCreated
{
    use Dispatchable, SerializesModels;

    public $questionId;

    public function __construct(string $questionId)
    {
        $this->questionId = $questionId;
    }
}
