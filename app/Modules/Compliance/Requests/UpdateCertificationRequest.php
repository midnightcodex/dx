<?php

namespace App\Modules\Compliance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'certification_type' => 'nullable|string|max:100',
            'certification_number' => 'nullable|string|max:100',
            'issuing_authority' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'scope' => 'nullable|string',
            'certificate_file_path' => 'nullable|string|max:500',
            'status' => 'nullable|string|max:20',
            'next_audit_date' => 'nullable|date',
        ];
    }
}
