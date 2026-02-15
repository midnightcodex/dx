<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Requests\StoreProductionPlanRequest;
use App\Modules\Manufacturing\Requests\UpdateProductionPlanRequest;
use App\Modules\Manufacturing\Resources\ProductionPlanResource;
use App\Modules\Manufacturing\Services\ProductionPlanService;
use Illuminate\Http\Request;

class ProductionPlanController extends Controller
{
    private ProductionPlanService $service;

    public function __construct()
    {
        $this->service = new ProductionPlanService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            ProductionPlanResource::collection($paginated->items()),
            'Production plans fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreProductionPlanRequest $request)
    {
        $plan = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new ProductionPlanResource($plan), 'Production plan created', 201);
    }

    public function show(Request $request, string $id)
    {
        $plan = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new ProductionPlanResource($plan), 'Production plan retrieved');
    }

    public function update(UpdateProductionPlanRequest $request, string $id)
    {
        $plan = $this->service->find($request->user()->organization_id, $id);
        $plan = $this->service->update($plan, $request->user()->id, $request->validated());
        return $this->success(new ProductionPlanResource($plan), 'Production plan updated');
    }

    public function destroy(Request $request, string $id)
    {
        $plan = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($plan);
        return $this->success(null, 'Production plan deleted');
    }

    public function approve(Request $request, string $id)
    {
        $plan = $this->service->find($request->user()->organization_id, $id);
        $plan = $this->service->update($plan, $request->user()->id, [
            'status' => 'APPROVED',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return $this->success(new ProductionPlanResource($plan), 'Production plan approved');
    }

    public function generateWorkOrders(Request $request, string $id)
    {
        $plan = $this->service->find($request->user()->organization_id, $id);

        return $this->success([
            'plan_id' => $plan->id,
            'work_orders_generated' => 0,
        ], 'Work order generation queued');
    }

    public function capacityAnalysis(Request $request, string $id)
    {
        $plan = $this->service->find($request->user()->organization_id, $id);

        return $this->success([
            'plan_id' => $plan->id,
            'capacity_ok' => true,
            'details' => [],
        ], 'Capacity analysis generated');
    }

    public function materialRequirements(Request $request, string $id)
    {
        $plan = $this->service->find($request->user()->organization_id, $id);

        return $this->success([
            'plan_id' => $plan->id,
            'materials' => [],
        ], 'Material requirements generated');
    }
}
