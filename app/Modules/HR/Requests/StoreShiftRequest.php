<?php

namespace App\Modules\HR\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_code' => 'required|string|max:50',
            'shift_name' => 'nullable|string|max:100',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'break_duration_minutes' => 'nullable|integer|min:0',
            'is_night_shift' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
