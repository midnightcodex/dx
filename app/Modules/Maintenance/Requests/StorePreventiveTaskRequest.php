<?php

namespace App\Modules\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreventiveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_number' => 'nullable|string|max:50',
            'schedule_id' => 'nullable|uuid',
            'machine_id' => 'nullable|uuid',
            'scheduled_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'assigned_to' => 'nullable|uuid',
            'performed_by' => 'nullable|uuid',
            'duration_minutes' => 'nullable|integer|min:0',
            'findings' => 'nullable|string',
            'actions_taken' => 'nullable|string',
        ];
    }
}
