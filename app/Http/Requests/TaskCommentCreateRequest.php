<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskCommentCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'comment' => [
                'required',
                'string',
            ],
            'parent_comment_id' => [
                'nullable',
                'integer',
                'exists:task_comments,id',
            ],
            'mentions' => [
                'nullable',
                'array',
            ],
            'mentions.*' => [
                'integer',
                'exists:users,id',
            ],
            'attachments' => [
                'nullable',
                'array',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comment' => trim($this->comment ?? ''),
        ]);
    }
}
