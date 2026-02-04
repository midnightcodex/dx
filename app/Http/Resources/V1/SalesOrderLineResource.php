<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this->item_id,
            'item_name' => $this->item->name ?? 'Unknown', // Eager load check needed
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total' => (float) $this->line_amount,
        ];
    }
}
