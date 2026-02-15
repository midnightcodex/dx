<?php

namespace App\Modules\Compliance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'certification_type' => $this->certification_type,
            'certification_number' => $this->certification_number,
            'issuing_authority' => $this->issuing_authority,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date,
            'scope' => $this->scope,
            'certificate_file_path' => $this->certificate_file_path,
            'status' => $this->status,
            'next_audit_date' => $this->next_audit_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
