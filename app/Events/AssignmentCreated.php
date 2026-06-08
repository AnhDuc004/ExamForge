<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentCreated
{
    use Dispatchable, SerializesModels;

    public $assignmentId;

    public function __construct(string $assignmentId)
    {
        $this->assignmentId = $assignmentId;
    }
}
