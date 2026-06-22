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

    protected function errorResponse(string $message = 'Error', $errors = null, ?string $errorCode = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'data' => null,
            'errors' => $errors,
        ];
    }
}
