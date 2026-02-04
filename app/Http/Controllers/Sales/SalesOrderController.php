<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Services\SalesService;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    protected SalesService $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'ilike', "%{$search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
            });
        }

        return Inertia::render('Sales/SalesOrders', [
            'orders' => $query->latest()->paginate(15),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Sales/SalesOrderCreate', [
            'customers' => Customer::where('is_active', true)->get(['id', 'name', 'payment_terms']),
            'items' => Item::where('is_active', true)->get(['id', 'item_code', 'name', 'sale_price']),
            'warehouses' => Warehouse::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:sales.customers,id',
            'order_date' => 'required|date',
            'expected_ship_date' => 'nullable|date|after_or_equal:order_date',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:inventory.items,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $so = $this->salesService->createOrder(
            $validated,
            auth()->id(),
            auth()->user()->organization_id
        );

        return redirect()->route('sales.orders.show', $so->id)
            ->with('message', "Sales Order {$so->so_number} created.");
    }

    public function show($id)
    {
        $order = SalesOrder::with(['customer', 'lines.item', 'shipments.lines', 'shipments.warehouse'])->findOrFail($id);

        return Inertia::render('Sales/SalesOrderShow', [
            'order' => $order,
            'warehouses' => Warehouse::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function confirm($id)
    {
        $so = SalesOrder::findOrFail($id);
        if ($so->status !== 'DRAFT') {
            return back()->withErrors(['status' => 'Only Draft orders can be confirmed.']);
        }
        $so->update(['status' => 'CONFIRMED']);
        return back()->with('message', 'Order Confirmed.');
    }

    public function ship(Request $request, $id)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:inventory.warehouses,id',
            'shipment_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.line_id' => 'required|exists:sales.sales_order_lines,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        $so = SalesOrder::with('lines')->findOrFail($id);

        $this->salesService->shipOrder(
            $so,
            $request->warehouse_id,
            $request->shipment_date,
            $request->lines,
            auth()->id()
        );

        return back()->with('message', 'Shipment processed successfully.');
    }
}
