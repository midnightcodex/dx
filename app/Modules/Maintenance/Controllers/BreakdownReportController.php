<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Requests\AssignBreakdownReportRequest;
use App\Modules\Maintenance\Requests\ResolveBreakdownReportRequest;
use App\Modules\Maintenance\Requests\StoreBreakdownReportRequest;
use App\Modules\Maintenance\Requests\UpdateBreakdownReportRequest;
use App\Modules\Maintenance\Resources\BreakdownReportResource;
use App\Modules\Maintenance\Services\BreakdownReportService;
use Illuminate\Http\Request;

class BreakdownReportController extends Controller
{
    private BreakdownReportService $service;

    public function __construct()
    {
        $this->service = new BreakdownReportService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            BreakdownReportResource::collection($paginated->items()),
            'Breakdown reports fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function open(Request $request)
    {
        $reports = $this->service->openReports($request->user()->organization_id);
        return $this->success(BreakdownReportResource::collection($reports), 'Open breakdown reports fetched');
    }

    public function store(StoreBreakdownReportRequest $request)
    {
        $report = $this->service->create(
            $request->user()->organization_id,
            $request->user()->id,
            $request->validated()
        );

        return $this->success(new BreakdownReportResource($report), 'Breakdown report created', 201);
    }

    public function show(Request $request, string $id)
    {
        $report = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new BreakdownReportResource($report), 'Breakdown report retrieved');
    }

    public function update(UpdateBreakdownReportRequest $request, string $id)
    {
        $report = $this->service->find($request->user()->organization_id, $id);
        $report = $this->service->update($report, $request->user()->id, $request->validated());
        return $this->success(new BreakdownReportResource($report), 'Breakdown report updated');
    }

    public function assign(AssignBreakdownReportRequest $request, string $id)
    {
        $report = $this->service->find($request->user()->organization_id, $id);
        $report = $this->service->update($report, $request->user()->id, [
            'assigned_to' => $request->validated()['assigned_to'],
            'status' => 'ASSIGNED',
        ]);

        return $this->success(new BreakdownReportResource($report), 'Breakdown report assigned');
    }

    public function resolve(ResolveBreakdownReportRequest $request, string $id)
    {
        $report = $this->service->find($request->user()->organization_id, $id);
        $payload = $request->validated();
        $payload['status'] = 'RESOLVED';
        $payload['resolved_at'] = $payload['resolved_at'] ?? now();

        $report = $this->service->update($report, $request->user()->id, $payload);

        return $this->success(new BreakdownReportResource($report), 'Breakdown report resolved');
    }

    public function destroy(Request $request, string $id)
    {
        $report = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($report);
        return $this->success(null, 'Breakdown report deleted');
    }
}
