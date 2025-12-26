<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class BootstrapCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                // Uniqueness checked in service to handle super admin case
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:128',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'password_confirmation' => [
                'required',
                'string',
                'same:password',
            ],
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^\S.*\S$|^\S$/', // At least one non-whitespace character
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^\S.*\S$|^\S$/', // At least one non-whitespace character
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
            'first_name' => trim($this->first_name ?? ''),
            'last_name' => trim($this->last_name ?? ''),
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'password.mixed' => 'The password must contain at least one uppercase and one lowercase letter.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one special character.',
            'first_name.regex' => 'The first name must contain at least one non-whitespace character.',
            'last_name.regex' => 'The last name must contain at least one non-whitespace character.',
        ];
    }
}
