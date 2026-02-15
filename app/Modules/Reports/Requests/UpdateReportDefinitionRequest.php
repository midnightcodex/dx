<?php

namespace App\Modules\Reports\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_code' => 'sometimes|string|max:50',
            'report_name' => 'nullable|string|max:255',
            'report_category' => 'nullable|string|max:50',
            'sql_query' => 'nullable|string',
            'parameters' => 'nullable|array',
            'is_system_report' => 'boolean',
        ];
    }
}
