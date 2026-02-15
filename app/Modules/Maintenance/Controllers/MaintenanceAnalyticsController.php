<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Models\BreakdownReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceAnalyticsController extends Controller
{
    public function downtime(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $query = BreakdownReport::query()->where('organization_id', $orgId);
        if ($from) {
            $query->whereDate('reported_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('reported_at', '<=', $to);
        }

        $total = (int) $query->sum('downtime_minutes');

        return $this->success([
            'total_downtime_minutes' => $total,
        ], 'Downtime analytics generated');
    }

    public function mttr(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $avg = BreakdownReport::query()
            ->where('organization_id', $orgId)
            ->whereNotNull('resolved_at')
            ->avg('downtime_minutes');

        return $this->success([
            'mttr_minutes' => $avg ? round((float) $avg, 2) : null,
        ], 'MTTR analytics generated');
    }

    public function mtbf(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $reports = BreakdownReport::query()
            ->select('machine_id', DB::raw('MIN(reported_at) as first_report'), DB::raw('MAX(reported_at) as last_report'), DB::raw('COUNT(*) as total'))
            ->where('organization_id', $orgId)
            ->groupBy('machine_id')
            ->get();

        $mtbfValues = [];
        foreach ($reports as $row) {
            if ($row->total < 2 || !$row->first_report || !$row->last_report) {
                continue;
            }
            $hours = Carbon::parse($row->first_report)->diffInHours(Carbon::parse($row->last_report));
            $mtbfValues[] = $hours / max(1, $row->total - 1);
        }

        $mtbf = count($mtbfValues) > 0 ? round(array_sum($mtbfValues) / count($mtbfValues), 2) : null;

        return $this->success([
            'mtbf_hours' => $mtbf,
        ], 'MTBF analytics generated');
    }
}
