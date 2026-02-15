<?php

namespace App\Modules\Manufacturing\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductionPlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'plan_number' => $this->plan_number,
            'plan_date' => $this->plan_date,
            'planning_period_start' => $this->planning_period_start,
            'planning_period_end' => $this->planning_period_end,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
