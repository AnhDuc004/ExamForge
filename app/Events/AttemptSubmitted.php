<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttemptSubmitted
{
    use Dispatchable, SerializesModels;

    public $attemptId;

    public function __construct(string $attemptId)
    {
        $this->attemptId = $attemptId;
    }
}
