<?php

namespace App\Modules\Integrations\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'integration_name' => 'required|string|max:100',
            'api_endpoint' => 'nullable|string|max:500',
            'auth_type' => 'nullable|string|max:50',
            'credentials' => 'nullable|array',
            'is_active' => 'boolean',
            'last_sync_at' => 'nullable|date',
            'sync_frequency_minutes' => 'nullable|integer|min:0',
        ];
    }
}
