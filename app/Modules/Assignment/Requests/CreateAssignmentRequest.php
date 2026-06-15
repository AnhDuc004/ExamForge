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
            'assignee_id' => ['required', 'uuid', 'exists:users,id'],
            'due_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after:now'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:100'],
            'access_type' => ['required', 'string', 'in:account,token'],
        ];
    }

    public function messages(): array
    {
        return [
            'test_id.required' => 'Test ID is required',
            'test_id.uuid' => 'Test ID must be a valid UUID',
            'test_id.exists' => 'Test not found',
            'assignee_id.required' => 'Assignee ID is required',
            'assignee_id.uuid' => 'Assignee ID must be a valid UUID',
            'assignee_id.exists' => 'Assignee not found',
            'due_at.date_format' => 'Due date must be in format Y-m-d H:i:s',
            'due_at.after' => 'Due date must be in the future',
            'max_attempts.integer' => 'Max attempts must be a number',
            'max_attempts.min' => 'Max attempts must be at least 1',
            'max_attempts.max' => 'Max attempts cannot exceed 100',
            'access_type.required' => 'Access type is required',
            'access_type.in' => 'Access type must be either account or token',
        ];
    }
}
