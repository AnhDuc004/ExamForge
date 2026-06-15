<?php

namespace App\Modules\Test\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'duration_seconds' => ['sometimes', 'integer', 'min:1'],
            'passing_score' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
