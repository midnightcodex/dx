<?php

namespace App\Modules\Compliance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_number' => 'sometimes|string|max:50',
            'document_name' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:50',
            'version' => 'nullable|string|max:20',
            'revision_number' => 'nullable|integer|min:1',
            'department' => 'nullable|string|max:100',
            'effective_date' => 'nullable|date',
            'review_date' => 'nullable|date',
            'next_review_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'file_path' => 'nullable|string|max:500',
            'reviewed_by' => 'nullable|uuid',
            'approved_by' => 'nullable|uuid',
            'approved_at' => 'nullable|date',
        ];
    }
}
