<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status'      => ['sometimes', 'in:pending,progress,done'],
            'user_id'     => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'due_date'    => ['sometimes', 'nullable', 'date'],
        ];
    }
}
