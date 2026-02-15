<?php

namespace App\Modules\Reports\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteCustomReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_code' => 'nullable|string|max:50',
            'parameters' => 'nullable|array',
        ];
    }
}
