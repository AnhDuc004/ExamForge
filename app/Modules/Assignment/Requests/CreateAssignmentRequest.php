<?php

namespace App\Modules\Assignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'test_id' => ['required', 'uuid', 'exists:tests,id'],
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'due_date' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:now'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'test_id.required' => 'Test ID is required',
            'test_id.uuid' => 'Test ID must be a valid UUID',
            'test_id.exists' => 'Test not found',
            'user_id.required' => 'User ID is required',
            'user_id.uuid' => 'User ID must be a valid UUID',
            'user_id.exists' => 'User not found',
            'due_date.date_format' => 'Due date must be in format Y-m-d H:i:s',
            'due_date.after' => 'Due date must be in the future',
            'max_attempts.integer' => 'Max attempts must be a number',
            'max_attempts.min' => 'Max attempts must be at least 1',
            'max_attempts.max' => 'Max attempts cannot exceed 100',
        ];
    }
}
