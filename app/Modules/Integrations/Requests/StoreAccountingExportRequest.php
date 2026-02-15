<?php

namespace App\Modules\Integrations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountingExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'export_number' => 'required|string|max:50',
            'export_date' => 'nullable|date',
            'export_type' => 'required|string|max:50',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'reference_ids' => 'nullable|array',
            'file_path' => 'nullable|string|max:500',
            'file_format' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:20',
            'exported_by' => 'nullable|uuid',
            'exported_at' => 'nullable|date',
            'error_message' => 'nullable|string',
        ];
    }
}
