<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyCreateRequest extends FormRequest
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
            ],
            // Password removed - Company Owner will set password when accepting invitation
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^\S.*\S$|^\S$/',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^\S.*\S$|^\S$/',
            ],
            'company_name' => [
                'required',
                'string',
                'max:255',
                // Uniqueness checked in service (checks companies table)
            ],
            'subdomain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                // Uniqueness checked in service (checks companies table)
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
            'company_name' => trim($this->company_name ?? ''),
            'subdomain' => strtolower(trim($this->subdomain ?? '')),
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
            'first_name.regex' => 'The first name must contain at least one non-whitespace character.',
            'last_name.regex' => 'The last name must contain at least one non-whitespace character.',
            'company_name.unique' => 'This company name is already taken.',
            'subdomain.required' => 'Subdomain is required.',
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
            'subdomain.unique' => 'This subdomain is already taken.',
        ];
    }
}
