<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'street' => 'required|string|max:255',
            'apartment' => 'nullable|string|max:50',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|regex:/^[a-zA-Z0-9\s\-]{1,20}$/',
            'country' => 'nullable|string|max:100',
            'landmark' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'postal_code.regex' => 'Postal code must be alphanumeric with spaces and hyphens only (max 20 characters).',
        ];
    }
}
