<?php

namespace App\Modules\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreventiveScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_code' => 'sometimes|string|max:50',
            'machine_id' => 'sometimes|uuid',
            'frequency_type' => 'nullable|string|max:20',
            'frequency_value' => 'nullable|integer|min:1',
            'checklist_template_id' => 'nullable|uuid',
            'last_performed_date' => 'nullable|date',
            'next_due_date' => 'nullable|date',
            'assigned_to' => 'nullable|uuid',
            'is_active' => 'boolean',
        ];
    }
}
