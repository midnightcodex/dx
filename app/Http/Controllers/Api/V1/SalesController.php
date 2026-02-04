<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function orders(Request $request)
    {
        $orders = SalesOrder::with(['lines.item', 'customer'])
            ->where('organization_id', $request->user()->organization_id)
            ->latest()
            ->paginate(15);

        return \App\Http\Resources\V1\SalesOrderResource::collection($orders);
    }

    public function storeOrder(Request $request)
    {
        // Simplified API for external systems (e.g. Shopify)
        $validated = $request->validate([
            'customer_id' => 'required|exists:sales.customers,id',
            'order_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:inventory.items,id',
            'lines.*.quantity' => 'required|numeric|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $so = SalesOrder::create([
                'organization_id' => $request->user()->organization_id,
                'customer_id' => $validated['customer_id'],
                'so_number' => 'API-' . date('Ymd-His'),
                'order_date' => $validated['order_date'],
                'status' => 'DRAFT',
                'total_amount' => 0, // Recalculated
                'created_by' => $request->user()->id,
            ]);

            $total = 0;
            foreach ($validated['lines'] as $line) {
                $lineTotal = $line['quantity'] * $line['unit_price']; // Assuming inclusive tax for simplicity
                SalesOrderLine::create([
                    'organization_id' => $so->organization_id,
                    'sales_order_id' => $so->id,
                    'item_id' => $line['item_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tax_rate' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);
                $total += $lineTotal;
            }

            $so->update(['total_amount' => $total]);

            return response()->json(['message' => 'Order created', 'id' => $so->id, 'so_number' => $so->so_number], 201);
        });
    }
}
