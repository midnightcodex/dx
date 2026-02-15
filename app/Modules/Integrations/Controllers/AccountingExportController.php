<?php

namespace App\Modules\Integrations\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Requests\ExportAccountingRequest;
use App\Modules\Integrations\Requests\StoreAccountingExportRequest;
use App\Modules\Integrations\Resources\AccountingExportResource;
use App\Modules\Integrations\Services\AccountingExportService;
use Illuminate\Http\Request;

class AccountingExportController extends Controller
{
    public function __construct(private AccountingExportService $service)
    {
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            AccountingExportResource::collection($paginated->items()),
            'Accounting exports fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreAccountingExportRequest $request)
    {
        $export = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new AccountingExportResource($export), 'Accounting export created', 201);
    }

    public function show(Request $request, string $id)
    {
        $export = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new AccountingExportResource($export), 'Accounting export retrieved');
    }

    public function exportInvoices(ExportAccountingRequest $request)
    {
        $export = $this->service->runInvoiceExport($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new AccountingExportResource($export), 'Invoice export generated', 201);
    }

    public function exportStockValuation(ExportAccountingRequest $request)
    {
        $export = $this->service->runStockValuationExport($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new AccountingExportResource($export), 'Stock valuation export generated', 201);
    }

    public function download(Request $request, string $id)
    {
        $export = $this->service->find($request->user()->organization_id, $id);

        return $this->success([
            'file_path' => $export->file_path,
            'file_format' => $export->file_format,
            'status' => $export->status,
        ], 'Export download info');
    }
}
