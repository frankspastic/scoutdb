<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFamilyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'street_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
            'primary_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Family name is required.',
            'name.max' => 'Family name must not exceed 255 characters.',
            'state.max' => 'State must be 2 characters (e.g., TX).',
            'zip.max' => 'Zip code must not exceed 10 characters.',
            'primary_phone.max' => 'Phone number must not exceed 20 characters.',
        ];
    }
}
