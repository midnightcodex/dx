<?php

namespace App\Modules\Manufacturing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_number' => 'sometimes|string|max:50',
            'plan_date' => 'nullable|date',
            'planning_period_start' => 'nullable|date',
            'planning_period_end' => 'nullable|date',
            'status' => 'nullable|string|max:20',
        ];
    }
}
