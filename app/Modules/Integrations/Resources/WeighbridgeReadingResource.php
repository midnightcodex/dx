<?php

namespace App\Modules\Integrations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WeighbridgeReadingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'reading_number' => $this->reading_number,
            'reading_date' => $this->reading_date,
            'vehicle_number' => $this->vehicle_number,
            'tare_weight' => $this->tare_weight,
            'gross_weight' => $this->gross_weight,
            'net_weight' => $this->net_weight,
            'uom' => $this->uom,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'weighbridge_operator' => $this->weighbridge_operator,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
