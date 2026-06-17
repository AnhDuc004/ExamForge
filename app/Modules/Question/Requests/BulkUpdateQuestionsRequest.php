<?php

namespace App\Modules\Question\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class BulkUpdateQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['uuid', 'exists:questions,id'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'max:50'],
            'difficulty' => ['sometimes', 'string', 'in:easy,medium,hard'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$this->has('tags') && !$this->has('difficulty')) {
                $validator->errors()->add('tags', 'Ít nhất phải có tags hoặc difficulty để cập nhật hàng loạt.');
            }
        });
    }
}
