<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'so_number' => $this->so_number,
            'status' => $this->status,
            'customer' => [
                'name' => $this->customer->name,
                'email' => $this->customer->email
            ],
            'dates' => [
                'ordered' => $this->order_date->format('Y-m-d'),
                'expected_ship' => $this->expected_ship_date?->format('Y-m-d'),
            ],
            'financials' => [
                'subtotal' => (float) $this->subtotal,
                'tax' => (float) $this->tax_amount,
                'total' => (float) $this->total_amount,
                'currency' => $this->currency ?? 'INR',
            ],
            'lines' => SalesOrderLineResource::collection($this->whenLoaded('lines')),
        ];
    }
}
