<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'email', 'max:255'],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
            'role_ids' => ['sometimes', 'array'],
            'role_ids.*' => ['uuid', 'exists:roles,id'],
        ];
    }
}
