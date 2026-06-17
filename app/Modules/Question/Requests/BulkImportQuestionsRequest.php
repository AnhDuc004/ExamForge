<?php

namespace App\Modules\Question\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkImportQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questions' => ['required_without:file', 'array', 'min:1'],
            'questions.*.type' => ['required_with:questions', 'string', 'in:multiple_choice,short_answer,essay,true_false'],
            'questions.*.content' => ['required_with:questions', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.correct_answer' => ['nullable', 'array'],
            'questions.*.max_score' => ['required_with:questions', 'integer', 'min:1', 'max:1000'],
            'questions.*.difficulty' => ['required_with:questions', 'string', 'in:easy,medium,hard'],
            'questions.*.tags' => ['nullable', 'array'],
            'questions.*.tags.*' => ['string', 'max:50'],
            'file' => ['required_without:questions', 'file', 'mimes:csv,txt'],
        ];
    }
}
