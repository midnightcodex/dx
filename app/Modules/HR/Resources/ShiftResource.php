<?php

namespace App\Modules\HR\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'shift_code' => $this->shift_code,
            'shift_name' => $this->shift_name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'break_duration_minutes' => $this->break_duration_minutes,
            'is_night_shift' => $this->is_night_shift,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
