<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => $this->category?->name,
            'uom' => $this->uom?->code,
            'sale_price' => (float) $this->sale_price,
            // Hiding cost_price for public/external API
            'stock_status' => $this->stock_status, // Enum or logic
        ];
    }
}
