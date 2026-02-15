<?php

namespace App\Modules\Compliance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Compliance\Requests\StoreCertificationRequest;
use App\Modules\Compliance\Requests\UpdateCertificationRequest;
use App\Modules\Compliance\Resources\CertificationResource;
use App\Modules\Compliance\Services\CertificationService;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    private CertificationService $service;

    public function __construct()
    {
        $this->service = new CertificationService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            CertificationResource::collection($paginated->items()),
            'Certifications fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreCertificationRequest $request)
    {
        $cert = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new CertificationResource($cert), 'Certification created', 201);
    }

    public function show(Request $request, string $id)
    {
        $cert = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new CertificationResource($cert), 'Certification retrieved');
    }

    public function update(UpdateCertificationRequest $request, string $id)
    {
        $cert = $this->service->find($request->user()->organization_id, $id);
        $cert = $this->service->update($cert, $request->user()->id, $request->validated());
        return $this->success(new CertificationResource($cert), 'Certification updated');
    }

    public function destroy(Request $request, string $id)
    {
        $cert = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($cert);
        return $this->success(null, 'Certification deleted');
    }
}
