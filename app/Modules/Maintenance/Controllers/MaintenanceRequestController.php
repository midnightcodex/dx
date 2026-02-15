<?php

namespace App\Modules\Maintenance\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Crud\CrudService;
use App\Modules\Maintenance\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    private CrudService $service;

    public function __construct()
    {
        $this->service = new CrudService(MaintenanceRequest::class);
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Maintenance requests fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|uuid',
            'request_type' => 'required|string|max:50',
            'status' => 'nullable|string|max:20',
            'priority' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'reported_by' => 'nullable|uuid',
            'assigned_to' => 'nullable|uuid',
            'scheduled_at' => 'nullable|date',
        ]);

        $req = $this->service->create($request->user()->organization_id, $request->user()->id, $validated);
        return $this->success($req, 'Maintenance request created', 201);
    }

    public function show(Request $request, string $id)
    {
        $req = $this->service->find($request->user()->organization_id, $id);
        return $this->success($req, 'Maintenance request retrieved');
    }

    public function update(Request $request, string $id)
    {
        $req = $this->service->find($request->user()->organization_id, $id);
        $validated = $request->validate([
            'status' => 'nullable|string|max:20',
            'priority' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|uuid',
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        $req = $this->service->update($req, $request->user()->id, $validated);
        return $this->success($req, 'Maintenance request updated');
    }

    public function destroy(Request $request, string $id)
    {
        $req = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($req);
        return $this->success(null, 'Maintenance request deleted');
    }
}
