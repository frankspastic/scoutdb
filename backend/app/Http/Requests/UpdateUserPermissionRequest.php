<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => 'sometimes|required|in:admin,editor,viewer',
            'granted_by' => 'nullable|integer|exists:user_permissions,id',
            'granted_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role must be one of: admin, editor, viewer.',
            'granted_by.exists' => 'The granting user permission does not exist.',
        ];
    }
}
