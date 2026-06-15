<?php

namespace App\Modules\Assignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'due_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:now'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'string', 'in:assigned,started,completed,expired,archived'],
            'access_type' => ['nullable', 'string', 'in:account,token'],
        ];
    }

    public function messages(): array
    {
        return [
            'due_at.date_format' => 'Due date must be in format Y-m-d H:i:s',
            'due_at.after' => 'Due date must be in the future',
            'max_attempts.integer' => 'Max attempts must be a number',
            'max_attempts.min' => 'Max attempts must be at least 1',
            'max_attempts.max' => 'Max attempts cannot exceed 100',
            'status.in' => 'Status must be one of: assigned, started, completed, expired, archived',
            'access_type.in' => 'Access type must be one of: account, token',
        ];
    }
}
