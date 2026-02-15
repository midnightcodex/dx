<?php

namespace App\Modules\Integrations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportAccountingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'export_number' => 'required|string|max:50',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'file_format' => 'nullable|string|max:20',
        ];
    }
}
