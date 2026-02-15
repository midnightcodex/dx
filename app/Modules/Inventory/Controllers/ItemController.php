<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\ItemService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function __construct(private ItemService $service)
    {
    }

    /**
     * Display a listing of items.
     */
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $filters = $request->only(['search', 'type']);
        $perPage = (int) $request->input('per_page', 15);

        $paginated = $this->service->list($orgId, $filters, $perPage);

        return $this->success(
            $paginated->items(),
            'Items fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    /**
     * List active items (for dropdowns).
     */
    public function active(Request $request)
    {
        $orgId = auth()->user()->organization_id;
        $items = $this->service->listActive($orgId);

        return $this->success($items, 'Items fetched');
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $validated = $request->validate([
            'item_code' => 'required|unique:inventory.items,item_code,NULL,id,organization_id,' . $orgId,
            'name' => 'required|string|max:255',
            'primary_uom_id' => [
                'required',
                Rule::exists('shared.uom', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('shared.item_categories', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'item_type' => 'required|in:STOCKABLE,SERVICE,CONSUMABLE',
            'stock_type' => 'nullable|in:RAW_MATERIAL,WIP,FINISHED_GOOD,SPARE_PART',
            'is_batch_tracked' => 'boolean',
            'is_serial_tracked' => 'boolean',
            'standard_cost' => 'nullable|numeric',
        ]);

        $item = $this->service->create(
            $orgId,
            auth()->id(),
            $validated
        );

        return $this->success($item, 'Item created', 201);
    }

    /**
     * Display the specified item.
     */
    public function show(string $id)
    {
        $item = $this->service->find(auth()->user()->organization_id, $id);
        return $this->success($item, 'Item retrieved');
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, string $id)
    {
        $orgId = auth()->user()->organization_id;
        $item = $this->service->find($orgId, $id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'item_type' => 'sometimes|required|in:STOCKABLE,SERVICE,CONSUMABLE',
            'standard_cost' => 'nullable|numeric',
            'description' => 'nullable|string',
            'primary_uom_id' => [
                'sometimes',
                Rule::exists('shared.uom', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('shared.item_categories', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'stock_type' => 'nullable|in:RAW_MATERIAL,WIP,FINISHED_GOOD,SPARE_PART',
            'is_batch_tracked' => 'boolean',
            'is_serial_tracked' => 'boolean',
        ]);

        $item = $this->service->update($item, auth()->id(), $validated);

        return $this->success($item, 'Item updated');
    }

    /**
     * Remove the specified item (soft delete).
     */
    public function destroy(string $id)
    {
        $item = $this->service->find(auth()->user()->organization_id, $id);
        $this->service->delete($item);
        return $this->success(null, 'Item deleted');
    }

    public function stockLevels(string $id)
    {
        $orgId = auth()->user()->organization_id;
        $this->service->find($orgId, $id);

        $levels = $this->service->stockLevels($orgId, $id);
        return $this->success($levels, 'Item stock levels');
    }

    public function transactionHistory(Request $request, string $id)
    {
        $orgId = auth()->user()->organization_id;
        $this->service->find($orgId, $id);

        $limit = (int) $request->input('limit', 50);
        $history = $this->service->transactionHistory($orgId, $id, min(200, max(1, $limit)));

        return $this->success($history, 'Item transaction history');
    }
}
