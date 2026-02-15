<?php

namespace App\Modules\Procurement\Services;

use App\Modules\Procurement\Models\PurchaseOrder;
use App\Modules\Procurement\Models\PurchaseOrderLine;
use App\Modules\Shared\Services\ApprovalWorkflowService;
use App\Modules\Shared\Services\NumberSeriesService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderFlowService
{
    public function __construct(
        private NumberSeriesService $numberSeriesService,
        private ApprovalWorkflowService $approvalWorkflowService
    ) {
    }

    public function list(string $organizationId, int $perPage = 15)
    {
        return PurchaseOrder::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function find(string $organizationId, string $id): PurchaseOrder
    {
        return PurchaseOrder::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function create(string $organizationId, string $userId, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($organizationId, $userId, $data) {
            $po = PurchaseOrder::create([
                'organization_id' => $organizationId,
                'po_number' => $data['po_number'] ?? $this->numberSeriesService->next(
                    $organizationId,
                    'PURCHASE_ORDER',
                    ['prefix' => 'PO-', 'padding' => 6, 'date_format' => 'yyyy']
                ),
                'vendor_id' => $data['vendor_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'delivery_warehouse_id' => $data['delivery_warehouse_id'],
                'status' => PurchaseOrder::STATUS_DRAFT,
                'currency' => $data['currency'] ?? 'INR',
                'payment_terms' => $data['payment_terms'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $subtotal = 0.0;
            $taxAmount = 0.0;
            $discountAmount = 0.0;

            foreach ($data['lines'] as $index => $line) {
                $qty = (float) $line['quantity'];
                $price = (float) $line['unit_price'];
                $taxRate = (float) ($line['tax_rate'] ?? 0);
                $discRate = (float) ($line['discount_percentage'] ?? 0);

                $baseAmount = $qty * $price;
                $discAmount = $baseAmount * ($discRate / 100);
                $netAmount = $baseAmount - $discAmount;
                $lineTax = $netAmount * ($taxRate / 100);
                $lineAmount = $netAmount + $lineTax;

                PurchaseOrderLine::create([
                    'organization_id' => $organizationId,
                    'purchase_order_id' => $po->id,
                    'line_number' => $line['line_number'] ?? ($index + 1),
                    'item_id' => $line['item_id'],
                    'description' => $line['description'] ?? null,
                    'quantity' => $qty,
                    'uom_id' => $line['uom_id'] ?? null,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'discount_percentage' => $discRate,
                    'discount_amount' => $discAmount,
                    'line_amount' => $lineAmount,
                    'expected_date' => $line['expected_date'] ?? null,
                ]);

                $subtotal += $netAmount;
                $taxAmount += $lineTax;
                $discountAmount += $discAmount;
            }

            $po->subtotal = $subtotal;
            $po->tax_amount = $taxAmount;
            $po->discount_amount = $discountAmount;
            $po->total_amount = $subtotal + $taxAmount;
            $po->save();

            return $po->load('lines');
        });
    }

    public function update(string $organizationId, string $userId, string $id, array $data): PurchaseOrder
    {
        $po = $this->find($organizationId, $id);

        if (!in_array($po->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_SUBMITTED], true)) {
            throw new \RuntimeException('PO can only be edited in DRAFT or SUBMITTED status.');
        }

        $po->fill(array_filter([
            'expected_date' => $data['expected_date'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ], static fn($v) => $v !== null));
        $po->save();

        return $po->refresh()->load('lines');
    }

    public function submit(string $organizationId, string $userId, string $id): PurchaseOrder
    {
        $po = $this->find($organizationId, $id);

        if ($po->status !== PurchaseOrder::STATUS_DRAFT) {
            throw new \RuntimeException('Only DRAFT POs can be submitted.');
        }

        $po->status = PurchaseOrder::STATUS_SUBMITTED;
        $po->save();

        $this->approvalWorkflowService->requestApproval(
            organizationId: $organizationId,
            requestedBy: $userId,
            entityType: 'PURCHASE_ORDER',
            entityId: $po->id,
            fromStatus: PurchaseOrder::STATUS_DRAFT,
            toStatus: PurchaseOrder::STATUS_APPROVED,
            amount: (float) $po->total_amount
        );

        return $po->refresh()->load('lines');
    }

    public function approve(string $organizationId, string $userId, string $id): PurchaseOrder
    {
        $po = $this->find($organizationId, $id);

        if ($po->status !== PurchaseOrder::STATUS_SUBMITTED) {
            throw new \RuntimeException('Only SUBMITTED POs can be approved.');
        }

        $po->status = PurchaseOrder::STATUS_APPROVED;
        $po->approved_by = $userId;
        $po->approved_at = now();
        $po->save();

        return $po->refresh()->load('lines');
    }

    public function cancel(string $organizationId, string $userId, string $id): PurchaseOrder
    {
        $po = $this->find($organizationId, $id);

        if (in_array($po->status, [PurchaseOrder::STATUS_COMPLETED, PurchaseOrder::STATUS_CANCELLED], true)) {
            throw new \RuntimeException('Completed/cancelled POs cannot be cancelled.');
        }

        $po->status = PurchaseOrder::STATUS_CANCELLED;
        $po->save();

        return $po->refresh()->load('lines');
    }

    public function updateReceiptStatus(PurchaseOrder $po): PurchaseOrder
    {
        $po->load('lines');

        $lines = $po->lines;
        $allReceived = $lines->count() > 0;
        $anyReceived = false;

        foreach ($lines as $line) {
            $received = (float) $line->received_quantity;
            $qty = (float) $line->quantity;
            if ($received > 0) {
                $anyReceived = true;
            }
            if ($received + 0.0001 < $qty) {
                $allReceived = false;
            }
        }

        if ($allReceived && $anyReceived) {
            $po->status = PurchaseOrder::STATUS_COMPLETED;
        } elseif ($anyReceived) {
            $po->status = PurchaseOrder::STATUS_PARTIAL;
        }

        $po->save();
        return $po->refresh();
    }
}
