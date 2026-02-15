<?php

namespace App\Modules\Integrations\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Requests\StoreWeighbridgeReadingRequest;
use App\Modules\Integrations\Resources\WeighbridgeReadingResource;
use App\Modules\Integrations\Services\WeighbridgeService;
use Illuminate\Http\Request;

class WeighbridgeController extends Controller
{
    private WeighbridgeService $service;

    public function __construct()
    {
        $this->service = new WeighbridgeService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            WeighbridgeReadingResource::collection($paginated->items()),
            'Weighbridge readings fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreWeighbridgeReadingRequest $request)
    {
        $reading = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new WeighbridgeReadingResource($reading), 'Weighbridge reading created', 201);
    }

    public function show(Request $request, string $id)
    {
        $reading = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new WeighbridgeReadingResource($reading), 'Weighbridge reading retrieved');
    }
}
