<?php

namespace App\Modules\Maintenance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PreventiveScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'schedule_code' => $this->schedule_code,
            'machine_id' => $this->machine_id,
            'frequency_type' => $this->frequency_type,
            'frequency_value' => $this->frequency_value,
            'checklist_template_id' => $this->checklist_template_id,
            'last_performed_date' => $this->last_performed_date,
            'next_due_date' => $this->next_due_date,
            'assigned_to' => $this->assigned_to,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
