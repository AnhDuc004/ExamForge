<?php

namespace App\Modules\Permission\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource' => ['sometimes', 'string', 'max:255'],
            'action' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
