<?php

namespace App\Modules\HR\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid',
            'attendance_date' => 'nullable|date',
            'shift_id' => 'nullable|uuid',
            'clock_in_time' => 'nullable|date',
            'status' => 'nullable|string|max:20',
        ];
    }
}
