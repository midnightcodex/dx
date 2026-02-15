<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Requests\StoreMachineRequest;
use App\Modules\Maintenance\Requests\UpdateMachineRequest;
use App\Modules\Maintenance\Resources\MachineResource;
use App\Modules\Maintenance\Resources\BreakdownReportResource;
use App\Modules\Maintenance\Services\MachineService;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    private MachineService $service;

    public function __construct()
    {
        $this->service = new MachineService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            MachineResource::collection($paginated->items()),
            'Machines fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreMachineRequest $request)
    {
        $machine = $this->service->create(
            $request->user()->organization_id,
            $request->user()->id,
            $request->validated()
        );
        return $this->success(new MachineResource($machine), 'Machine created', 201);
    }

    public function show(Request $request, string $id)
    {
        $machine = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new MachineResource($machine), 'Machine retrieved');
    }

    public function history(Request $request, string $id)
    {
        $history = $this->service->history($request->user()->organization_id, $id);
        return $this->success(BreakdownReportResource::collection($history), 'Machine history fetched');
    }

    public function update(UpdateMachineRequest $request, string $id)
    {
        $machine = $this->service->find($request->user()->organization_id, $id);
        $machine = $this->service->update($machine, $request->user()->id, $request->validated());
        return $this->success(new MachineResource($machine), 'Machine updated');
    }

    public function destroy(Request $request, string $id)
    {
        $machine = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($machine);
        return $this->success(null, 'Machine deleted');
    }
}
