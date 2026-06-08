<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse(string $message = 'Success', $data = null, int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    protected function errorResponse(string $message = 'Error', $errors = null, int $code = 400): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }
}
