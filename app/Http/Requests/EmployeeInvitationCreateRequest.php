<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeInvitationCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
            ],
            'role' => [
                'required',
                'string',
                'in:admin,user,super_admin',
            ],
            'department' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'joined_date' => ['nullable', 'date'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
            'first_name' => trim($this->first_name ?? ''),
            'last_name' => trim($this->last_name ?? ''),
        ]);
    }

    public function messages(): array
    {
        return [
            'role.in' => 'The role must be one of: admin, user, super_admin.',
        ];
    }
}
