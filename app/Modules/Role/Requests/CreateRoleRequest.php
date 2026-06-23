<?php

namespace App\Modules\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'permission_ids' => ['sometimes', 'array'],
            'permission_ids.*' => ['uuid', 'exists:permissions,id'],
        ];
    }
}
