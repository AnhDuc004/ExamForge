<?php

namespace App\Modules\Test\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTestSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'position' => ['required', 'integer', 'min:1'],
            'questions' => ['sometimes', 'array'],
            'questions.*.question_id' => ['required_with:questions', 'uuid', 'exists:questions,id'],
            'questions.*.position' => ['required_with:questions', 'integer', 'min:1'],
            'questions.*.score_override' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }
}
