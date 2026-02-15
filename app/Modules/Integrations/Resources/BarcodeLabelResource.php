<?php

namespace App\Modules\Integrations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BarcodeLabelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'label_type' => $this->label_type,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'barcode' => $this->barcode,
            'barcode_format' => $this->barcode_format,
            'generated_at' => $this->generated_at,
            'printed_at' => $this->printed_at,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
