<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'sometimes',
                Rule::in(['todo', 'in_progress', 'review', 'done', 'archived']),
            ],
            'priority' => [
                'sometimes',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'due_date' => [
                'nullable',
                'date',
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
            'tags' => [
                'nullable',
                'array',
            ],
            'tags.*' => [
                'string',
                'max:50',
            ],
            'checklist' => [
                'nullable',
                'array',
            ],
            'checklist.*.text' => [
                'required_with:checklist',
                'string',
            ],
            'checklist.*.checked' => [
                'boolean',
            ],
            'is_pinned' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim($this->title ?? ''),
            'description' => trim($this->description ?? ''),
        ]);
    }
}
