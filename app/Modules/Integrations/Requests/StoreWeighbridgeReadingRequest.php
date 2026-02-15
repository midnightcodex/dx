<?php

namespace App\Modules\Integrations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeighbridgeReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reading_number' => 'required|string|max:50',
            'reading_date' => 'nullable|date',
            'vehicle_number' => 'nullable|string|max:50',
            'tare_weight' => 'nullable|numeric|min:0',
            'gross_weight' => 'nullable|numeric|min:0',
            'net_weight' => 'nullable|numeric|min:0',
            'uom' => 'nullable|string|max:10',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|uuid',
            'weighbridge_operator' => 'nullable|uuid',
        ];
    }
}
