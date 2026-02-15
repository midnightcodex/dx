<?php

namespace App\Modules\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBreakdownReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_number' => 'required|string|max:50',
            'machine_id' => 'required|uuid',
            'reported_at' => 'nullable|date',
            'reported_by' => 'nullable|uuid',
            'problem_description' => 'nullable|string',
            'severity' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:20',
        ];
    }
}
