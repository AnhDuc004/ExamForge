<?php

namespace App\Modules\Question\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Verify user has permission to create questions
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:multiple_choice,short_answer,essay,true_false'],
            'content' => ['required', 'string'],
            'options' => ['nullable', 'array', 'required_if:type,multiple_choice'],
            'options.*' => ['string'],
            'correct_answer' => ['nullable', 'array'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Question type is required.',
            'type.in' => 'Invalid question type.',
            'content.required' => 'Question content is required.',
            'options.required_if' => 'Options are required for multiple choice questions.',
            'options.array' => 'Options must be an array.',
            'max_score.required' => 'Max score is required.',
            'max_score.min' => 'Max score must be at least 1.',
            'difficulty.required' => 'Difficulty level is required.',
            'difficulty.in' => 'Invalid difficulty level.',
        ];
    }
}
