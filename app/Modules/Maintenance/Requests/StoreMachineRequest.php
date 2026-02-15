<?php

namespace App\Modules\Maintenance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'machine_code' => 'required|string|max:50',
            'machine_name' => 'required|string|max:255',
            'machine_type' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'warranty_expiry_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'work_center_id' => 'nullable|uuid',
            'capacity' => 'nullable|string|max:100',
            'power_rating' => 'nullable|string|max:100',
            'maintenance_frequency_days' => 'nullable|integer|min:0',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ];
    }
}
