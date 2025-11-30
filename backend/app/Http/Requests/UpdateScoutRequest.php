<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'person_id' => 'sometimes|required|integer|exists:persons,id',
            'grade' => 'nullable|string|max:20',
            'rank' => 'nullable|string|max:50',
            'den' => 'nullable|string|max:50',
            'registration_expiration_date' => 'nullable|date',
            'registration_status' => 'nullable|in:active,inactive,suspended',
            'ypt_status' => 'nullable|in:pending,completed,expired',
            'program' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'person_id.exists' => 'The selected person does not exist.',
            'registration_status.in' => 'Registration status must be: active, inactive, or suspended.',
            'ypt_status.in' => 'YPT status must be: pending, completed, or expired.',
        ];
    }
}
