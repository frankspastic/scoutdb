<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $person_id = $this->route('person');

        return [
            'family_id' => 'nullable|integer|exists:families,id',
            'person_type' => 'sometimes|required|in:scout,parent,sibling,adult_leader',
            'bsa_member_id' => "nullable|string|max:20|unique:persons,bsa_member_id,{$person_id}",
            'prefix' => 'nullable|string|max:10',
            'first_name' => 'sometimes|required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'suffix' => 'nullable|string|max:10',
            'nickname' => 'nullable|string|max:255',
            'gender' => 'nullable|in:M,F,Other',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer|min:0|max:150',
            'email' => "nullable|email|unique:persons,email,{$person_id}",
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'person_type.in' => 'Person type must be one of: scout, parent, sibling, adult_leader.',
            'email.unique' => 'This email address is already in use.',
            'bsa_member_id.unique' => 'This BSA member ID is already in use.',
            'date_of_birth.date' => 'Date of birth must be a valid date.',
            'age.max' => 'Age must not exceed 150.',
        ];
    }
}
