<?php

namespace App\Modules\Integrations\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Requests\StoreApiConfigurationRequest;
use App\Modules\Integrations\Requests\UpdateApiConfigurationRequest;
use App\Modules\Integrations\Resources\ApiConfigurationResource;
use App\Modules\Integrations\Services\ApiConfigurationService;
use Illuminate\Http\Request;

class ApiConfigurationController extends Controller
{
    private ApiConfigurationService $service;

    public function __construct()
    {
        $this->service = new ApiConfigurationService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            ApiConfigurationResource::collection($paginated->items()),
            'API configurations fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreApiConfigurationRequest $request)
    {
        $config = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new ApiConfigurationResource($config), 'API configuration created', 201);
    }

    public function show(Request $request, string $id)
    {
        $config = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new ApiConfigurationResource($config), 'API configuration retrieved');
    }

    public function update(UpdateApiConfigurationRequest $request, string $id)
    {
        $config = $this->service->find($request->user()->organization_id, $id);
        $config = $this->service->update($config, $request->user()->id, $request->validated());
        return $this->success(new ApiConfigurationResource($config), 'API configuration updated');
    }

    public function destroy(Request $request, string $id)
    {
        $config = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($config);
        return $this->success(null, 'API configuration deleted');
    }
}
