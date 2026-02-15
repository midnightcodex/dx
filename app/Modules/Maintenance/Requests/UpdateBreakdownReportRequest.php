<?php

namespace App\Modules\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBreakdownReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'problem_description' => 'nullable|string',
            'severity' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:20',
            'assigned_to' => 'nullable|uuid',
            'work_started_at' => 'nullable|date',
            'work_completed_at' => 'nullable|date',
            'downtime_minutes' => 'nullable|integer|min:0',
            'production_loss_estimate' => 'nullable|numeric|min:0',
            'root_cause' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'preventive_action' => 'nullable|string',
            'spare_parts_used' => 'nullable|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'resolved_by' => 'nullable|uuid',
            'resolved_at' => 'nullable|date',
        ];
    }
}
