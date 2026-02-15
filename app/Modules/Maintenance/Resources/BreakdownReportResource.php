<?php

namespace App\Modules\Maintenance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BreakdownReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'ticket_number' => $this->ticket_number,
            'machine_id' => $this->machine_id,
            'reported_at' => $this->reported_at,
            'reported_by' => $this->reported_by,
            'problem_description' => $this->problem_description,
            'severity' => $this->severity,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'work_started_at' => $this->work_started_at,
            'work_completed_at' => $this->work_completed_at,
            'downtime_minutes' => $this->downtime_minutes,
            'production_loss_estimate' => $this->production_loss_estimate,
            'root_cause' => $this->root_cause,
            'corrective_action' => $this->corrective_action,
            'preventive_action' => $this->preventive_action,
            'spare_parts_used' => $this->spare_parts_used,
            'labor_cost' => $this->labor_cost,
            'parts_cost' => $this->parts_cost,
            'total_cost' => $this->total_cost,
            'resolved_by' => $this->resolved_by,
            'resolved_at' => $this->resolved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
