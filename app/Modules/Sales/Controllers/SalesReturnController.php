<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Requests\StoreSalesReturnRequest;
use App\Modules\Sales\Requests\UpdateSalesReturnRequest;
use App\Modules\Sales\Resources\SalesReturnResource;
use App\Modules\Sales\Services\SalesReturnService;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    private SalesReturnService $service;

    public function __construct()
    {
        $this->service = new SalesReturnService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            SalesReturnResource::collection($paginated->items()),
            'Sales returns fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreSalesReturnRequest $request)
    {
        $return = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new SalesReturnResource($return), 'Sales return created', 201);
    }

    public function show(Request $request, string $id)
    {
        $return = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new SalesReturnResource($return), 'Sales return retrieved');
    }

    public function update(UpdateSalesReturnRequest $request, string $id)
    {
        $return = $this->service->find($request->user()->organization_id, $id);
        $return = $this->service->update($return, $request->user()->id, $request->validated());
        return $this->success(new SalesReturnResource($return), 'Sales return updated');
    }

    public function destroy(Request $request, string $id)
    {
        $return = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($return);
        return $this->success(null, 'Sales return deleted');
    }
}
