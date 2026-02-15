<?php

namespace App\Modules\Reports\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportDefinitionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'report_code' => $this->report_code,
            'report_name' => $this->report_name,
            'report_category' => $this->report_category,
            'sql_query' => $this->sql_query,
            'parameters' => $this->parameters,
            'is_system_report' => $this->is_system_report,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}
