<?php

namespace App\Modules\Compliance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'document_number' => $this->document_number,
            'document_name' => $this->document_name,
            'document_type' => $this->document_type,
            'version' => $this->version,
            'revision_number' => $this->revision_number,
            'department' => $this->department,
            'effective_date' => $this->effective_date,
            'review_date' => $this->review_date,
            'next_review_date' => $this->next_review_date,
            'status' => $this->status,
            'file_path' => $this->file_path,
            'created_by' => $this->created_by,
            'reviewed_by' => $this->reviewed_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
