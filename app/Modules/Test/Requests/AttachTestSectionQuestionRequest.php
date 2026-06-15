<?php

namespace App\Modules\Test\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachTestSectionQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'uuid', 'exists:questions,id'],
            'position' => ['required', 'integer', 'min:1'],
            'score_override' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }
}
