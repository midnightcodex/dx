<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Services\ScrapService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScrapController extends Controller
{
    public function __construct(private ScrapService $service)
    {
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Scrap entries fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $validated = $request->validate([
            'scrap_number' => 'nullable|string|max:50',
            'source_type' => 'nullable|string|max:50',
            'source_id' => 'nullable|uuid',
            'item_id' => ['required', Rule::exists('inventory.items', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'warehouse_id' => ['required', Rule::exists('inventory.warehouses', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'batch_id' => ['nullable', Rule::exists('inventory.batches', 'id')->where(fn($q) => $q->where('organization_id', $orgId))],
            'scrap_quantity' => 'required|numeric|min:0.0001',
            'scrap_value' => 'nullable|numeric|min:0',
            'scrap_reason' => 'nullable|string|max:255',
            'scrap_category' => 'nullable|string|max:50',
            'disposal_method' => 'nullable|string|max:50',
            'disposed_quantity' => 'nullable|numeric|min:0',
            'disposal_date' => 'nullable|date',
        ]);

        $scrap = $this->service->create($orgId, $request->user()->id, $validated);
        return $this->success($scrap, 'Scrap entry created', 201);
    }

    public function dispose(Request $request, string $id)
    {
        $validated = $request->validate([
            'disposal_method' => 'required|string|max:50',
            'disposed_quantity' => 'required|numeric|min:0',
            'disposal_date' => 'nullable|date',
        ]);

        $scrap = $this->service->dispose($request->user()->organization_id, $request->user()->id, $id, $validated);
        return $this->success($scrap, 'Scrap disposal recorded');
    }

    public function recover(Request $request, string $id)
    {
        $validated = $request->validate([
            'recovered_item_id' => 'nullable|uuid',
            'recovered_quantity' => 'required|numeric|min:0.0001',
            'recovery_value' => 'nullable|numeric|min:0',
            'recovery_date' => 'nullable|date',
            'sold_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $recovery = $this->service->recover($request->user()->organization_id, $request->user()->id, $id, $validated);
        return $this->success($recovery, 'Scrap recovery recorded');
    }

    public function analysis(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 100));
        $rows = collect($paginated->items())
            ->groupBy('item_id')
            ->map(function ($items, $itemId) {
                return [
                    'item_id' => $itemId,
                    'total_scrap_quantity' => (float) collect($items)->sum('scrap_quantity'),
                    'total_scrap_value' => (float) collect($items)->sum('scrap_value'),
                ];
            })
            ->values();

        return $this->success($rows, 'Scrap analysis');
    }

    public function trends(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 100));
        $rows = collect($paginated->items())
            ->groupBy(fn($s) => optional($s->created_at)->format('Y-m'))
            ->map(fn($items, $month) => [
                'month' => $month,
                'scrap_quantity' => (float) collect($items)->sum('scrap_quantity'),
                'scrap_value' => (float) collect($items)->sum('scrap_value'),
            ])
            ->values();

        return $this->success($rows, 'Scrap trends');
    }
}
