<?php

namespace App\Modules\Test\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'position' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
