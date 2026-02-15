<?php

namespace App\Modules\Sales\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesReturnResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'return_number' => $this->return_number,
            'customer_id' => $this->customer_id,
            'sales_order_id' => $this->sales_order_id,
            'delivery_note_id' => $this->delivery_note_id,
            'return_date' => $this->return_date,
            'status' => $this->status,
            'reason' => $this->reason,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
