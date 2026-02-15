<?php

namespace App\Modules\HR\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'employee_id' => $this->employee_id,
            'attendance_date' => $this->attendance_date,
            'shift_id' => $this->shift_id,
            'clock_in_time' => $this->clock_in_time,
            'clock_out_time' => $this->clock_out_time,
            'work_duration_minutes' => $this->work_duration_minutes,
            'status' => $this->status,
            'late_arrival_minutes' => $this->late_arrival_minutes,
            'early_departure_minutes' => $this->early_departure_minutes,
            'overtime_minutes' => $this->overtime_minutes,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
