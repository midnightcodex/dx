<?php

namespace App\Modules\Integrations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountingExportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'export_number' => $this->export_number,
            'export_date' => $this->export_date,
            'export_type' => $this->export_type,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'reference_ids' => $this->reference_ids,
            'file_path' => $this->file_path,
            'file_format' => $this->file_format,
            'status' => $this->status,
            'exported_by' => $this->exported_by,
            'exported_at' => $this->exported_at,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
