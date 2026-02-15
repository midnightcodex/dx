<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Requests\ClockInRequest;
use App\Modules\HR\Requests\ClockOutRequest;
use App\Modules\HR\Resources\AttendanceResource;
use App\Modules\HR\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private AttendanceService $service;

    public function __construct()
    {
        $this->service = new AttendanceService();
    }

    public function index(Request $request)
    {
        $records = $this->service->dailyReport(
            $request->user()->organization_id,
            $request->input('date', now()->toDateString())
        );
        return $this->success(AttendanceResource::collection($records), 'Attendance fetched');
    }

    public function clockIn(ClockInRequest $request)
    {
        $attendance = $this->service->clockIn($request->user()->organization_id, $request->validated());
        return $this->success(new AttendanceResource($attendance), 'Clock-in recorded', 201);
    }

    public function clockOut(ClockOutRequest $request)
    {
        $attendance = $this->service->clockOut($request->user()->organization_id, $request->validated());
        return $this->success(new AttendanceResource($attendance), 'Clock-out recorded');
    }

    public function dailyReport(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $records = $this->service->dailyReport($request->user()->organization_id, $date);
        return $this->success(AttendanceResource::collection($records), 'Daily attendance report');
    }

    public function monthlyReport(Request $request, string $employeeId)
    {
        $records = $this->service->monthlyReport($request->user()->organization_id, $employeeId);
        return $this->success(AttendanceResource::collection($records), 'Monthly attendance report');
    }

    public function absentToday(Request $request)
    {
        $records = $this->service->absentToday($request->user()->organization_id);
        return $this->success(AttendanceResource::collection($records), 'Absent today report');
    }
}
