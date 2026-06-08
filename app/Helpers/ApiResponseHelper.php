<?php

namespace App\Helpers;

class ApiResponseHelper
{
    public static function success(string $message = 'Success', $data = null): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error(string $message = 'Error', $errors = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }
}
