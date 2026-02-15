<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Services\CostingService;
use Illuminate\Http\Request;

class CostingController extends Controller
{
    public function __construct(private CostingService $service)
    {
    }

    public function calculateWorkOrder(Request $request, string $woId)
    {
        $data = $this->service->calculateForWorkOrder($request->user()->organization_id, $woId);
        return $this->success($data, 'Work order cost calculated');
    }

    public function workOrderCost(Request $request, string $woId)
    {
        $data = $this->service->calculateForWorkOrder($request->user()->organization_id, $woId);
        return $this->success($data, 'Work order cost summary');
    }

    public function varianceAnalysis(Request $request)
    {
        $data = $this->service->varianceAnalysis($request->user()->organization_id);
        return $this->success($data, 'Cost variance analysis');
    }

    public function costTrends(Request $request)
    {
        $data = $this->service->varianceAnalysis($request->user()->organization_id);
        return $this->success($data, 'Cost trends');
    }
}
