<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\GoodsReceiptNote;
use App\Modules\Procurement\Models\GrnLine;
use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseOrderLine;
use App\Modules\Inventory\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GrnController extends Controller
{
    protected InventoryPostingService $inventoryService;

    public function __construct(InventoryPostingService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of GRNs.
     */
    public function index(Request $request)
    {
        $query = GoodsReceiptNote::with([
            'purchaseOrder:id,po_number',
            'vendor:id,vendor_code,name',
            'warehouse:id,code,name',
        ])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $grns = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => GoodsReceiptNote::count(),
            'pending' => GoodsReceiptNote::whereIn('status', ['DRAFT', 'INSPECTING'])->count(),
            'posted' => GoodsReceiptNote::where('status', 'POSTED')->count(),
        ];

        return Inertia::render('Procurement/GRN', [
            'grns' => $grns,
            'stats' => $stats,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Show the form for creating a new GRN.
     */
    public function create(Request $request)
    {
        // Get approved POs with pending quantities
        $approvedPos = PurchaseOrder::where('status', PurchaseOrder::STATUS_APPROVED)
            ->orWhere('status', PurchaseOrder::STATUS_PARTIAL)
            ->with(['vendor:id,vendor_code,name', 'lines.item:id,item_code,name'])
            ->get()
            ->filter(function ($po) {
                // Only include POs with lines that have pending quantity
                return $po->lines->contains(fn($line) => $line->pending_quantity > 0);
            });

        // If a specific PO is requested, pre-select it
        $selectedPo = null;
        if ($request->filled('po_id')) {
            $selectedPo = PurchaseOrder::with(['vendor', 'lines.item'])
                ->findOrFail($request->po_id);
        }

        return Inertia::render('Procurement/GRNCreate', [
            'purchaseOrders' => $approvedPos,
            'selectedPo' => $selectedPo,
        ]);
    }

    /**
     * Store a newly created GRN.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:procurement.purchase_orders,id',
            'supplier_invoice_number' => 'nullable|string|max:100',
            'supplier_invoice_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.po_line_id' => 'required|exists:procurement.purchase_order_lines,id',
            'lines.*.received_quantity' => 'required|numeric|min:0',
            'lines.*.accepted_quantity' => 'required|numeric|min:0',
            'lines.*.rejected_quantity' => 'nullable|numeric|min:0',
            'lines.*.rejection_reason' => 'nullable|string|max:255',
        ]);

        $po = PurchaseOrder::with('lines')->findOrFail($validated['purchase_order_id']);

        // Validate received quantities don't exceed pending
        foreach ($validated['lines'] as $lineData) {
            $poLine = $po->lines->firstWhere('id', $lineData['po_line_id']);
            if (!$poLine) {
                return back()->withErrors(['lines' => 'Invalid PO line reference.']);
            }
            if ($lineData['received_quantity'] > $poLine->pending_quantity) {
                return back()->withErrors(['lines' => "Cannot receive more than pending quantity for {$poLine->item->name}."]);
            }
        }

        return DB::transaction(function () use ($validated, $po) {
            $grn = GoodsReceiptNote::create([
                'organization_id' => auth()->user()->organization_id,
                'grn_number' => $this->generateGrnNumber(),
                'purchase_order_id' => $po->id,
                'vendor_id' => $po->vendor_id,
                'warehouse_id' => $po->delivery_warehouse_id,
                'receipt_date' => now(),
                'status' => GoodsReceiptNote::STATUS_DRAFT,
                'supplier_invoice_number' => $validated['supplier_invoice_number'] ?? null,
                'supplier_invoice_date' => $validated['supplier_invoice_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $lineData) {
                $poLine = PurchaseOrderLine::find($lineData['po_line_id']);

                GrnLine::create([
                    'organization_id' => auth()->user()->organization_id,
                    'grn_id' => $grn->id,
                    'po_line_id' => $poLine->id,
                    'item_id' => $poLine->item_id,
                    'ordered_quantity' => $poLine->quantity,
                    'received_quantity' => $lineData['received_quantity'],
                    'accepted_quantity' => $lineData['accepted_quantity'],
                    'rejected_quantity' => $lineData['rejected_quantity'] ?? 0,
                    'rejection_reason' => $lineData['rejection_reason'] ?? null,
                    'unit_price' => $poLine->unit_price,
                    'quality_status' => 'PENDING',
                ]);
            }

            return redirect()->route('procurement.grn.show', $grn->id)
                ->with('message', "GRN {$grn->grn_number} created. Ready for posting.");
        });
    }

    /**
     * Display the specified GRN.
     */
    public function show(string $id)
    {
        $grn = GoodsReceiptNote::with([
            'purchaseOrder.vendor',
            'warehouse',
            'lines.item:id,item_code,name',
            'receivedBy:id,name',
        ])->findOrFail($id);

        return Inertia::render('Procurement/GRNShow', [
            'grn' => $grn,
        ]);
    }

    /**
     * Post the GRN - THIS IS WHERE INVENTORY IS UPDATED.
     */
    public function post(string $id)
    {
        $grn = GoodsReceiptNote::with(['lines', 'purchaseOrder'])->findOrFail($id);

        if (!$grn->canBePosted()) {
            return back()->withErrors(['status' => 'This GRN cannot be posted.']);
        }

        return DB::transaction(function () use ($grn) {
            // Post each line to inventory
            foreach ($grn->lines as $line) {
                if ($line->accepted_quantity <= 0) {
                    continue;
                }

                // Create stock transaction via InventoryPostingService
                $this->inventoryService->post(
                    transactionType: 'RECEIPT',
                    itemId: $line->item_id,
                    warehouseId: $grn->warehouse_id,
                    quantity: (float) $line->accepted_quantity, // Positive = receipt
                    unitCost: (float) $line->unit_price,
                    referenceType: 'GRN',
                    referenceId: $grn->id,
                    organizationId: $grn->organization_id
                );

                // Update PO line received quantity
                $poLine = PurchaseOrderLine::find($line->po_line_id);
                if ($poLine) {
                    $poLine->received_quantity = (float) $poLine->received_quantity + (float) $line->accepted_quantity;
                    $poLine->save();
                }

                // Mark line as passed QC
                $line->update(['quality_status' => 'PASSED']);
            }

            // Update GRN status
            $grn->update([
                'status' => GoodsReceiptNote::STATUS_POSTED,
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            // Update PO status
            $po = $grn->purchaseOrder;
            $po->refresh();
            if ($po->isFullyReceived()) {
                $po->update(['status' => PurchaseOrder::STATUS_COMPLETED]);
            } else {
                $po->update(['status' => PurchaseOrder::STATUS_PARTIAL]);
            }

            return back()->with('message', "GRN {$grn->grn_number} posted. Stock updated successfully!");
        });
    }

    /**
     * Generate unique GRN number.
     */
    private function generateGrnNumber(): string
    {
        $prefix = 'GRN-' . now()->format('Ym') . '-';
        $lastGrn = GoodsReceiptNote::where('grn_number', 'like', $prefix . '%')
            ->orderByDesc('grn_number')
            ->first();
        $nextNum = $lastGrn ? (int) substr($lastGrn->grn_number, -4) + 1 : 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
