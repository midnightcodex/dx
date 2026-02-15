<?php

namespace App\Modules\Maintenance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PreventiveTaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'task_number' => $this->task_number,
            'schedule_id' => $this->schedule_id,
            'machine_id' => $this->machine_id,
            'scheduled_date' => $this->scheduled_date,
            'completed_date' => $this->completed_date,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'performed_by' => $this->performed_by,
            'duration_minutes' => $this->duration_minutes,
            'findings' => $this->findings,
            'actions_taken' => $this->actions_taken,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
