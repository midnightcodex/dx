<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Requests\StorePreventiveTaskRequest;
use App\Modules\Maintenance\Requests\UpdatePreventiveTaskRequest;
use App\Modules\Maintenance\Resources\PreventiveTaskResource;
use App\Modules\Maintenance\Services\PreventiveTaskService;
use Illuminate\Http\Request;

class PreventiveTaskController extends Controller
{
    private PreventiveTaskService $service;

    public function __construct()
    {
        $this->service = new PreventiveTaskService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            PreventiveTaskResource::collection($paginated->items()),
            'Preventive tasks fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function due(Request $request)
    {
        $tasks = $this->service->dueTasks($request->user()->organization_id);
        return $this->success(PreventiveTaskResource::collection($tasks), 'Due preventive tasks fetched');
    }

    public function store(StorePreventiveTaskRequest $request)
    {
        $task = $this->service->create(
            $request->user()->organization_id,
            $request->user()->id,
            $request->validated()
        );

        return $this->success(new PreventiveTaskResource($task), 'Preventive task created', 201);
    }

    public function show(Request $request, string $id)
    {
        $task = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new PreventiveTaskResource($task), 'Preventive task retrieved');
    }

    public function update(UpdatePreventiveTaskRequest $request, string $id)
    {
        $task = $this->service->find($request->user()->organization_id, $id);
        $task = $this->service->update($task, $request->user()->id, $request->validated());
        return $this->success(new PreventiveTaskResource($task), 'Preventive task updated');
    }

    public function complete(Request $request, string $id)
    {
        $task = $this->service->find($request->user()->organization_id, $id);
        $task = $this->service->update($task, $request->user()->id, [
            'status' => 'COMPLETED',
            'completed_date' => now()->toDateString(),
        ]);

        return $this->success(new PreventiveTaskResource($task), 'Preventive task completed');
    }

    public function destroy(Request $request, string $id)
    {
        $task = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($task);
        return $this->success(null, 'Preventive task deleted');
    }
}
