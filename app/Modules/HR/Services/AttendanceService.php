<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function clockIn(string $organizationId, array $data): Attendance
    {
        $attendanceDate = $data['attendance_date'] ?? now()->toDateString();
        $clockInTime = $data['clock_in_time'] ?? now();

        return Attendance::query()->updateOrCreate(
            [
                'organization_id' => $organizationId,
                'employee_id' => $data['employee_id'],
                'attendance_date' => $attendanceDate,
            ],
            [
                'shift_id' => $data['shift_id'] ?? null,
                'clock_in_time' => $clockInTime,
                'status' => $data['status'] ?? 'PRESENT',
            ]
        );
    }

    public function clockOut(string $organizationId, array $data): Attendance
    {
        $attendanceDate = $data['attendance_date'] ?? now()->toDateString();
        $clockOutTime = Carbon::parse($data['clock_out_time'] ?? now());

        $attendance = Attendance::query()->where('organization_id', $organizationId)
            ->where('employee_id', $data['employee_id'])
            ->where('attendance_date', $attendanceDate)
            ->firstOrFail();

        $clockIn = $attendance->clock_in_time ? Carbon::parse($attendance->clock_in_time) : null;
        $duration = $clockIn ? $clockIn->diffInMinutes($clockOutTime) : null;

        $attendance->update([
            'clock_out_time' => $clockOutTime,
            'work_duration_minutes' => $duration,
            'status' => $data['status'] ?? $attendance->status ?? 'PRESENT',
        ]);

        return $attendance->refresh();
    }

    public function dailyReport(string $organizationId, string $date): Collection
    {
        return Attendance::query()
            ->where('organization_id', $organizationId)
            ->where('attendance_date', $date)
            ->get();
    }

    public function monthlyReport(string $organizationId, string $employeeId): Collection
    {
        return Attendance::query()
            ->where('organization_id', $organizationId)
            ->where('employee_id', $employeeId)
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    public function absentToday(string $organizationId): Collection
    {
        $today = now()->toDateString();
        return Attendance::query()
            ->where('organization_id', $organizationId)
            ->where('attendance_date', $today)
            ->where('status', 'ABSENT')
            ->get();
    }
}
