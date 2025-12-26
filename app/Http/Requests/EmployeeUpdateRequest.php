<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        $employeeId = $this->route('id');

        return [
            'first_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'last_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'email' => [
                'sometimes',
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($employeeId),
            ],
            'role' => [
                'sometimes',
                'required',
                'string',
                'in:admin,user,super_admin',
            ],
            'department' => [
                'nullable',
                'string',
                'max:100',
            ],
            'designation' => [
                'nullable',
                'string',
                'max:100',
            ],
            'joined_date' => [
                'nullable',
                'date',
            ],
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['active', 'suspended', 'blocked']),
            ],
            'status_reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => isset($this->email) ? strtolower(trim($this->email)) : null,
            'first_name' => isset($this->first_name) ? trim($this->first_name) : null,
            'last_name' => isset($this->last_name) ? trim($this->last_name) : null,
        ]);
    }
}
