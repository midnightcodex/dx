<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Item;
use App\Modules\Shared\Models\Uom;
use App\Modules\Shared\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'primaryUom']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('item_code', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('item_type', $request->input('type'));
        }

        return response()->json(
            $query->paginate($request->input('per_page', 15))
        );
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|unique:inventory.items,item_code,NULL,id,organization_id,' . auth()->user()->organization_id,
            'name' => 'required|string|max:255',
            'primary_uom_id' => 'required|exists:shared.uom,id',
            'category_id' => 'nullable|exists:shared.item_categories,id',
            'item_type' => 'required|in:STOCKABLE,SERVICE,CONSUMABLE',
            'stock_type' => 'nullable|in:RAW_MATERIAL,WIP,FINISHED_GOOD,SPARE_PART',
            'is_batch_tracked' => 'boolean',
            'is_serial_tracked' => 'boolean',
            'standard_cost' => 'nullable|numeric',
        ]);

        $validated['organization_id'] = auth()->user()->organization_id;
        $validated['created_by'] = auth()->id();

        $item = Item::create($validated);

        return response()->json($item, 201);
    }

    /**
     * Display the specified item.
     */
    public function show(string $id)
    {
        $item = Item::with(['category', 'primaryUom'])->findOrFail($id);
        return response()->json($item);
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, string $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'item_type' => 'sometimes|required|in:STOCKABLE,SERVICE,CONSUMABLE',
            'standard_cost' => 'nullable|numeric',
            'description' => 'nullable|string',
            // Add other fields as needed
        ]);

        $validated['updated_by'] = auth()->id();

        $item->update($validated);

        return response()->json($item);
    }

    /**
     * Remove the specified item (soft delete).
     */
    public function destroy(string $id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
