<?php

namespace App\Modules\Test\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestSectionQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position' => ['sometimes', 'integer', 'min:1'],
            'score_override' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }
}
