<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    public function __construct(string $message = 'Authentication failed', int $code = 401)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
        ], $this->code);
    }
}
