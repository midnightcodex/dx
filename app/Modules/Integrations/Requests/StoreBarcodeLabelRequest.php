<?php

namespace App\Modules\Integrations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarcodeLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label_type' => 'nullable|string|max:50',
            'entity_type' => 'nullable|string|max:50',
            'entity_id' => 'nullable|uuid',
            'barcode' => 'required|string|max:255',
            'barcode_format' => 'nullable|string|max:20',
            'generated_at' => 'nullable|date',
            'printed_at' => 'nullable|date',
            'is_active' => 'boolean',
        ];
    }
}
