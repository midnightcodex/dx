<?php

namespace App\Modules\Integrations\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Integrations\Requests\StoreBarcodeLabelRequest;
use App\Modules\Integrations\Resources\BarcodeLabelResource;
use App\Modules\Integrations\Services\BarcodeLabelService;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function __construct(private BarcodeLabelService $service)
    {
    }

    public function generate(StoreBarcodeLabelRequest $request)
    {
        $label = $this->service->create($request->user()->organization_id, $request->user()->id, $request->validated());
        return $this->success(new BarcodeLabelResource($label), 'Barcode generated', 201);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $label = $this->service->findByBarcode(
            $request->user()->organization_id,
            $request->input('barcode')
        );

        return $this->success(new BarcodeLabelResource($label), 'Barcode scanned');
    }

    public function printBatch(Request $request)
    {
        $request->validate([
            'label_ids' => 'required|array',
            'label_ids.*' => 'uuid',
        ]);

        $labels = $this->service->findManyByIds(
            $request->user()->organization_id,
            $request->input('label_ids')
        );

        return $this->success(BarcodeLabelResource::collection($labels), 'Barcode print batch ready');
    }
}
