<?php

namespace App\Modules\Test\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
            'passing_score' => ['required', 'integer', 'min:0'],
            'sections' => ['sometimes', 'array'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.instructions' => ['sometimes', 'nullable', 'string'],
            'sections.*.position' => ['required', 'integer', 'min:1'],
            'sections.*.questions' => ['sometimes', 'array'],
            'sections.*.questions.*.question_id' => ['required', 'uuid', 'exists:questions,id'],
            'sections.*.questions.*.position' => ['required', 'integer', 'min:1'],
            'sections.*.questions.*.score_override' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }
}
