<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Requests\StoreReportDefinitionRequest;
use App\Modules\Reports\Requests\UpdateReportDefinitionRequest;
use App\Modules\Reports\Resources\ReportDefinitionResource;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Http\Request;

class ReportDefinitionController extends Controller
{
    private ReportService $service;

    public function __construct()
    {
        $this->service = new ReportService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            ReportDefinitionResource::collection($paginated->items()),
            'Report definitions fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreReportDefinitionRequest $request)
    {
        $definition = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new ReportDefinitionResource($definition), 'Report definition created', 201);
    }

    public function show(Request $request, string $id)
    {
        $definition = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new ReportDefinitionResource($definition), 'Report definition retrieved');
    }

    public function update(UpdateReportDefinitionRequest $request, string $id)
    {
        $definition = $this->service->find($request->user()->organization_id, $id);
        $definition = $this->service->update($definition, $request->user()->id, $request->validated());
        return $this->success(new ReportDefinitionResource($definition), 'Report definition updated');
    }

    public function destroy(Request $request, string $id)
    {
        $definition = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($definition);
        return $this->success(null, 'Report definition deleted');
    }
}
