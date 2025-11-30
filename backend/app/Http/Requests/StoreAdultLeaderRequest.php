<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdultLeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'person_id' => 'required|integer|exists:persons,id',
            'positions' => 'nullable|array',
            'positions.*' => 'string|max:100',
            'ypt_status' => 'nullable|in:pending,completed,expired',
            'ypt_completion_date' => 'nullable|date',
            'ypt_expiration_date' => 'nullable|date',
            'registration_expiration_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'person_id.required' => 'Person ID is required.',
            'person_id.exists' => 'The selected person does not exist.',
            'positions.array' => 'Positions must be an array.',
            'positions.*.string' => 'Each position must be a string.',
            'ypt_status.in' => 'YPT status must be: pending, completed, or expired.',
        ];
    }
}
