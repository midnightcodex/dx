<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Requests\ExecuteCustomReportRequest;
use App\Modules\Reports\Models\ReportDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportExecutionController extends Controller
{
    public function inventoryStockSummary(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $rows = DB::table('inventory.stock_ledger as sl')
            ->leftJoin('inventory.items as i', 'i.id', '=', 'sl.item_id')
            ->where('sl.organization_id', $orgId)
            ->select('sl.item_id', 'i.name as item_name', DB::raw('SUM(sl.quantity_available) as quantity_available'))
            ->groupBy('sl.item_id', 'i.name')
            ->get();

        return $this->success($rows, 'Stock summary generated');
    }

    public function manufacturingProductionSummary(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $rows = DB::table('manufacturing.work_orders')
            ->where('organization_id', $orgId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return $this->success($rows, 'Production summary generated');
    }

    public function salesAnalysis(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $rows = DB::table('sales.sales_orders')
            ->where('organization_id', $orgId)
            ->select('status', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('status')
            ->get();

        return $this->success($rows, 'Sales analysis generated');
    }

    public function executeCustom(ExecuteCustomReportRequest $request)
    {
        $orgId = $request->user()->organization_id;
        $reportCode = $request->input('report_code');

        if (!$reportCode) {
            return $this->success([], 'Custom report executed (no report_code provided)');
        }

        $definition = ReportDefinition::query()
            ->where('organization_id', $orgId)
            ->where('report_code', $reportCode)
            ->first();

        if (!$definition) {
            return $this->error('Report definition not found', 404, ['report_code' => ['Invalid report_code']], 'REPORT_NOT_FOUND');
        }

        return $this->success([
            'report_code' => $definition->report_code,
            'parameters' => $request->input('parameters', []),
            'data' => [],
        ], 'Custom report executed');
    }
}
