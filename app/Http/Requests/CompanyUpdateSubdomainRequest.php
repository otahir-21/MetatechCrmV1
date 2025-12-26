<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateSubdomainRequest extends FormRequest
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
            'subdomain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                'unique:users,subdomain,' . $this->route('id'),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
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
            'subdomain.required' => 'Subdomain is required.',
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
            'subdomain.unique' => 'This subdomain is already taken.',
        ];
    }
}

