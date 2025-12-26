<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffInvitationCreateRequest extends FormRequest
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
                Rule::in(['admin', 'project_manager', 'user']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
        ]);
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role must be one of: admin, project_manager, user',
        ];
    }
}
