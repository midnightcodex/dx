<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\Vendor;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseOrderLine;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['vendor:id,vendor_code,name', 'deliveryWarehouse:id,code,name'])
            ->orderByDesc('created_at');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Vendor filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total' => PurchaseOrder::count(),
            'pending' => PurchaseOrder::whereIn('status', ['DRAFT', 'SUBMITTED'])->count(),
            'approved' => PurchaseOrder::where('status', 'APPROVED')->count(),
            'completed' => PurchaseOrder::where('status', 'COMPLETED')->count(),
            'totalValue' => PurchaseOrder::sum('total_amount'),
        ];

        return Inertia::render('Procurement/PurchaseOrders', [
            'purchaseOrders' => $purchaseOrders,
            'stats' => $stats,
            'filters' => $request->only(['status', 'vendor_id']),
            'vendors' => Vendor::active()->get(['id', 'vendor_code', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        return Inertia::render('Procurement/PurchaseOrderCreate', [
            'vendors' => Vendor::active()->get(['id', 'vendor_code', 'name', 'payment_terms', 'currency']),
            'items' => Item::where('is_active', true)
                ->whereIn('item_type', ['RAW_MATERIAL', 'CONSUMABLE', 'SPARE_PART'])
                ->get(['id', 'item_code', 'name', 'default_uom_id']),
            'warehouses' => Warehouse::where('is_active', true)->get(['id', 'code', 'name']),
        ]);
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:procurement.vendors,id',
            'expected_date' => 'required|date|after_or_equal:today',
            'delivery_warehouse_id' => 'required|exists:inventory.warehouses,id',
            'payment_terms' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:inventory.items,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated) {
            $po = PurchaseOrder::create([
                'organization_id' => auth()->user()->organization_id,
                'po_number' => $this->generatePoNumber(),
                'vendor_id' => $validated['vendor_id'],
                'order_date' => now(),
                'expected_date' => $validated['expected_date'],
                'delivery_warehouse_id' => $validated['delivery_warehouse_id'],
                'status' => PurchaseOrder::STATUS_DRAFT,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'currency' => 'INR',
            ]);

            // Create lines
            $lineNumber = 1;
            foreach ($validated['lines'] as $lineData) {
                $taxRate = $lineData['tax_rate'] ?? 18;
                $baseAmount = $lineData['quantity'] * $lineData['unit_price'];
                $taxAmount = $baseAmount * ($taxRate / 100);
                $lineAmount = $baseAmount + $taxAmount;

                PurchaseOrderLine::create([
                    'organization_id' => auth()->user()->organization_id,
                    'purchase_order_id' => $po->id,
                    'line_number' => $lineNumber++,
                    'item_id' => $lineData['item_id'],
                    'description' => $lineData['description'] ?? null,
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_amount' => $lineAmount,
                    'received_quantity' => 0,
                ]);
            }

            // Calculate totals
            $po->refresh();
            $po->calculateTotals();
            $po->save();

            return redirect()->route('procurement.purchase-orders')
                ->with('message', "Purchase Order {$po->po_number} created successfully.");
        });
    }

    /**
     * Display the specified purchase order.
     */
    public function show(string $id)
    {
        $po = PurchaseOrder::with([
            'vendor',
            'deliveryWarehouse',
            'lines.item:id,item_code,name',
            'goodsReceiptNotes',
        ])->findOrFail($id);

        return Inertia::render('Procurement/PurchaseOrderShow', [
            'purchaseOrder' => $po,
        ]);
    }

    /**
     * Submit PO for approval.
     */
    public function submit(string $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->withErrors(['status' => 'Only draft POs can be submitted.']);
        }

        $po->update(['status' => PurchaseOrder::STATUS_SUBMITTED]);

        return back()->with('message', "PO {$po->po_number} submitted for approval.");
    }

    /**
     * Approve the purchase order.
     */
    public function approve(string $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if (!in_array($po->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_SUBMITTED])) {
            return back()->withErrors(['status' => 'Only draft or submitted POs can be approved.']);
        }

        $po->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('message', "PO {$po->po_number} approved successfully.");
    }

    /**
     * Cancel the purchase order.
     */
    public function cancel(string $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status === PurchaseOrder::STATUS_COMPLETED) {
            return back()->withErrors(['status' => 'Completed POs cannot be cancelled.']);
        }

        // Check if any GRN has been posted
        $hasPostedGrn = $po->goodsReceiptNotes()->where('status', 'POSTED')->exists();
        if ($hasPostedGrn) {
            return back()->withErrors(['status' => 'Cannot cancel PO with posted receipts.']);
        }

        $po->update(['status' => PurchaseOrder::STATUS_CANCELLED]);

        return back()->with('message', "PO {$po->po_number} cancelled.");
    }

    /**
     * Generate unique PO number.
     */
    private function generatePoNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ym') . '-';
        $lastPo = PurchaseOrder::where('po_number', 'like', $prefix . '%')
            ->orderByDesc('po_number')
            ->first();
        $nextNum = $lastPo ? (int) substr($lastPo->po_number, -4) + 1 : 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
