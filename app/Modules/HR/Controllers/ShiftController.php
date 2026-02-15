<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Requests\StoreShiftRequest;
use App\Modules\HR\Requests\UpdateShiftRequest;
use App\Modules\HR\Resources\ShiftResource;
use App\Modules\HR\Services\ShiftService;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    private ShiftService $service;

    public function __construct()
    {
        $this->service = new ShiftService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            ShiftResource::collection($paginated->items()),
            'Shifts fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreShiftRequest $request)
    {
        $shift = $this->service->create(
            $request->user()->organization_id,
            $request->user()->id,
            $request->validated()
        );
        return $this->success(new ShiftResource($shift), 'Shift created', 201);
    }

    public function show(Request $request, string $id)
    {
        $shift = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new ShiftResource($shift), 'Shift retrieved');
    }

    public function update(UpdateShiftRequest $request, string $id)
    {
        $shift = $this->service->find($request->user()->organization_id, $id);
        $shift = $this->service->update($shift, $request->user()->id, $request->validated());
        return $this->success(new ShiftResource($shift), 'Shift updated');
    }

    public function destroy(Request $request, string $id)
    {
        $shift = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($shift);
        return $this->success(null, 'Shift deleted');
    }
}
