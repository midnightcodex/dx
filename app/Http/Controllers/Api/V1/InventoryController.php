<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Item;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function items(Request $request)
    {
        $query = Item::with(['category', 'uom'])->where('organization_id', $request->user()->organization_id);

        if ($request->has('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%')
                ->orWhere('sku', 'ilike', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return ItemResource::collection(
            $query->latest()->paginate(20)
        );
    }

    public function itemDetail(Request $request, $id)
    {
        $item = Item::where('id', $id)
            ->where('organization_id', $request->user()->organization_id)
            ->firstOrFail();

        return response()->json($item);
    }
}
