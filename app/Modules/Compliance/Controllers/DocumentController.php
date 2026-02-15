<?php

namespace App\Modules\Compliance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Compliance\Requests\StoreDocumentRequest;
use App\Modules\Compliance\Requests\UpdateDocumentRequest;
use App\Modules\Compliance\Resources\DocumentResource;
use App\Modules\Compliance\Services\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    private DocumentService $service;

    public function __construct()
    {
        $this->service = new DocumentService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            DocumentResource::collection($paginated->items()),
            'Documents fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreDocumentRequest $request)
    {
        $doc = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new DocumentResource($doc), 'Document created', 201);
    }

    public function show(Request $request, string $id)
    {
        $doc = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new DocumentResource($doc), 'Document retrieved');
    }

    public function update(UpdateDocumentRequest $request, string $id)
    {
        $doc = $this->service->find($request->user()->organization_id, $id);
        $doc = $this->service->update($doc, $request->user()->id, $request->validated());
        return $this->success(new DocumentResource($doc), 'Document updated');
    }

    public function destroy(Request $request, string $id)
    {
        $doc = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($doc);
        return $this->success(null, 'Document deleted');
    }
}
