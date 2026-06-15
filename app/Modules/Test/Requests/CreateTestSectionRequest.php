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
        ];
    }
}
