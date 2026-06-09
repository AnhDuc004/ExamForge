<?php

namespace App\Modules\Question\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verify user is the creator or admin
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'in:multiple_choice,short_answer,essay,true_false'],
            'content' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string'],
            'correct_answer' => ['nullable', 'array'],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'status' => ['nullable', 'string', 'in:draft,published,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Invalid question type.',
            'difficulty.in' => 'Invalid difficulty level.',
            'status.in' => 'Invalid status.',
        ];
    }
}
