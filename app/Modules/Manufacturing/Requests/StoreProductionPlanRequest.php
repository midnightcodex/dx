<?php

namespace App\Modules\Manufacturing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_number' => 'required|string|max:50',
            'plan_date' => 'required|date',
            'planning_period_start' => 'required|date',
            'planning_period_end' => 'required|date',
            'status' => 'nullable|string|max:20',
        ];
    }
}
