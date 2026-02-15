<?php

namespace App\Modules\Maintenance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MachineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'machine_code' => $this->machine_code,
            'machine_name' => $this->machine_name,
            'machine_type' => $this->machine_type,
            'manufacturer' => $this->manufacturer,
            'model_number' => $this->model_number,
            'serial_number' => $this->serial_number,
            'purchase_date' => $this->purchase_date,
            'installation_date' => $this->installation_date,
            'warranty_expiry_date' => $this->warranty_expiry_date,
            'location' => $this->location,
            'work_center_id' => $this->work_center_id,
            'capacity' => $this->capacity,
            'power_rating' => $this->power_rating,
            'maintenance_frequency_days' => $this->maintenance_frequency_days,
            'last_maintenance_date' => $this->last_maintenance_date,
            'next_maintenance_date' => $this->next_maintenance_date,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
