<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CompanyOwnerInvitationAcceptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/service
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'token' => ['required', 'string', 'size:64'], // Plain token is 64 chars
            'first_name' => ['required', 'string', 'max:100', 'regex:/^\S.*\S$|^\S$/'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^\S.*\S$|^\S$/'],
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
            'password_confirmation' => ['required', 'string', 'same:password'],
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
}
