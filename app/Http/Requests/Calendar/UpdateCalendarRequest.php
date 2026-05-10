<?php

namespace App\Http\Requests\Calendar;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'google_calendar_id' => ['required', 'string', 'max:255'],
            'user_id'            => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
