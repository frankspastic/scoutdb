<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wordpress_user_id' => 'required|integer|unique:user_permissions',
            'person_id' => 'required|integer|exists:persons,id',
            'role' => 'required|in:admin,editor,viewer',
            'granted_by' => 'nullable|integer|exists:user_permissions,id',
            'granted_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'wordpress_user_id.required' => 'WordPress user ID is required.',
            'wordpress_user_id.unique' => 'This WordPress user already has a permission record.',
            'person_id.required' => 'Person ID is required.',
            'person_id.exists' => 'The selected person does not exist.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be one of: admin, editor, viewer.',
            'granted_by.exists' => 'The granting user permission does not exist.',
        ];
    }
}
