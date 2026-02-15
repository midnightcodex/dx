<?php

namespace App\Modules\HR\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClockOutRequest extends FormRequest
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
            'clock_out_time' => 'nullable|date',
            'status' => 'nullable|string|max:20',
        ];
    }
}
