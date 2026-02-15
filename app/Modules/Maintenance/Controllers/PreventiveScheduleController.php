<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Requests\StorePreventiveScheduleRequest;
use App\Modules\Maintenance\Requests\UpdatePreventiveScheduleRequest;
use App\Modules\Maintenance\Resources\PreventiveScheduleResource;
use App\Modules\Maintenance\Services\PreventiveScheduleService;
use Illuminate\Http\Request;

class PreventiveScheduleController extends Controller
{
    private PreventiveScheduleService $service;

    public function __construct()
    {
        $this->service = new PreventiveScheduleService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            PreventiveScheduleResource::collection($paginated->items()),
            'Preventive schedules fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StorePreventiveScheduleRequest $request)
    {
        $schedule = $this->service->create(
            $request->user()->organization_id,
            $request->user()->id,
            $request->validated()
        );

        return $this->success(new PreventiveScheduleResource($schedule), 'Preventive schedule created', 201);
    }

    public function show(Request $request, string $id)
    {
        $schedule = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new PreventiveScheduleResource($schedule), 'Preventive schedule retrieved');
    }

    public function update(UpdatePreventiveScheduleRequest $request, string $id)
    {
        $schedule = $this->service->find($request->user()->organization_id, $id);
        $schedule = $this->service->update($schedule, $request->user()->id, $request->validated());
        return $this->success(new PreventiveScheduleResource($schedule), 'Preventive schedule updated');
    }

    public function destroy(Request $request, string $id)
    {
        $schedule = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($schedule);
        return $this->success(null, 'Preventive schedule deleted');
    }
}
