<?php

namespace App\Modules\Integrations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiConfigurationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'integration_name' => $this->integration_name,
            'api_endpoint' => $this->api_endpoint,
            'auth_type' => $this->auth_type,
            'credentials' => $this->credentials,
            'is_active' => $this->is_active,
            'last_sync_at' => $this->last_sync_at,
            'sync_frequency_minutes' => $this->sync_frequency_minutes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
