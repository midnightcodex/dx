<?php

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Procurement\Requests\StorePurchaseInvoiceRequest;
use App\Modules\Procurement\Requests\UpdatePurchaseInvoiceRequest;
use App\Modules\Procurement\Resources\PurchaseInvoiceResource;
use App\Modules\Procurement\Services\PurchaseInvoiceService;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    private PurchaseInvoiceService $service;

    public function __construct()
    {
        $this->service = new PurchaseInvoiceService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            PurchaseInvoiceResource::collection($paginated->items()),
            'Purchase invoices fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StorePurchaseInvoiceRequest $request)
    {
        $invoice = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new PurchaseInvoiceResource($invoice), 'Purchase invoice created', 201);
    }

    public function show(Request $request, string $id)
    {
        $invoice = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new PurchaseInvoiceResource($invoice), 'Purchase invoice retrieved');
    }

    public function update(UpdatePurchaseInvoiceRequest $request, string $id)
    {
        $invoice = $this->service->find($request->user()->organization_id, $id);
        $invoice = $this->service->update($invoice, $request->user()->id, $request->validated());
        return $this->success(new PurchaseInvoiceResource($invoice), 'Purchase invoice updated');
    }

    public function destroy(Request $request, string $id)
    {
        $invoice = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($invoice);
        return $this->success(null, 'Purchase invoice deleted');
    }
}
