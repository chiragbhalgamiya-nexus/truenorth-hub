<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'nullable|string|max:100|min:2',
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore(auth()->id())
                    ->whereNull('deleted_at'),
            ],
            'phone' => 'nullable|string|max:20',
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
            'email.unique' => 'Email already in use.',
            'name.min' => 'Name must be at least 2 characters.',
        ];
    }
}
