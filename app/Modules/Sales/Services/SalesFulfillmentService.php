<?php

namespace App\Modules\Sales\Services;

use App\Modules\Inventory\Models\StockLedger;
use App\Modules\Inventory\Services\InventoryPostingService;
use App\Modules\Sales\Models\DeliveryNote;
use App\Modules\Sales\Models\DeliveryNoteLine;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderLine;
use App\Modules\Shared\Services\NumberSeriesService;
use Illuminate\Support\Facades\DB;

class SalesFulfillmentService
{
    public function __construct(
        private NumberSeriesService $numberSeriesService,
        private InventoryPostingService $inventoryPostingService
    ) {
    }

    public function listOrders(string $organizationId, int $perPage = 15)
    {
        return SalesOrder::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function findOrder(string $organizationId, string $id): SalesOrder
    {
        return SalesOrder::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function createOrder(string $organizationId, string $userId, array $data): SalesOrder
    {
        return DB::transaction(function () use ($organizationId, $userId, $data) {
            $order = SalesOrder::create([
                'organization_id' => $organizationId,
                'so_number' => $data['so_number'] ?? $this->numberSeriesService->next(
                    $organizationId,
                    'SALES_ORDER',
                    ['prefix' => 'SO-', 'padding' => 6, 'date_format' => 'yyyy']
                ),
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => SalesOrder::STATUS_DRAFT,
                'currency' => $data['currency'] ?? 'INR',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            $subtotal = 0.0;
            $taxAmount = 0.0;

            foreach ($data['lines'] as $index => $line) {
                $qty = (float) $line['quantity'];
                $unit = (float) $line['unit_price'];
                $tax = (float) ($line['tax_amount'] ?? 0);
                $lineAmount = (float) ($line['line_amount'] ?? (($qty * $unit) + $tax));

                SalesOrderLine::create([
                    'organization_id' => $organizationId,
                    'sales_order_id' => $order->id,
                    'line_number' => $line['line_number'] ?? ($index + 1),
                    'item_id' => $line['item_id'],
                    'quantity' => $qty,
                    'uom_id' => $line['uom_id'] ?? null,
                    'unit_price' => $unit,
                    'tax_amount' => $tax,
                    'line_amount' => $lineAmount,
                ]);

                $subtotal += $qty * $unit;
                $taxAmount += $tax;
            }

            $order->subtotal = $subtotal;
            $order->tax_amount = $taxAmount;
            $order->total_amount = $subtotal + $taxAmount - (float) $order->discount_amount;
            $order->save();

            return $order->load('lines');
        });
    }

    public function updateOrder(string $organizationId, string $userId, string $id, array $data): SalesOrder
    {
        $order = $this->findOrder($organizationId, $id);

        if (!in_array($order->status, [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CONFIRMED], true)) {
            throw new \RuntimeException('Order can only be updated in DRAFT/CONFIRMED status.');
        }

        $order->fill(array_filter([
            'expected_date' => $data['expected_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ], static fn($v) => $v !== null));
        $order->updated_by = $userId;
        $order->save();

        return $order->refresh()->load('lines');
    }

    public function confirmOrder(string $organizationId, string $userId, string $id): SalesOrder
    {
        $order = $this->findOrder($organizationId, $id);

        if ($order->status !== SalesOrder::STATUS_DRAFT) {
            throw new \RuntimeException('Only DRAFT orders can be confirmed.');
        }

        $order->status = SalesOrder::STATUS_CONFIRMED;
        $order->updated_by = $userId;
        $order->save();

        return $order->refresh()->load('lines');
    }

    public function reserveStock(string $organizationId, string $userId, string $orderId): SalesOrder
    {
        return DB::transaction(function () use ($organizationId, $userId, $orderId) {
            $order = $this->findOrder($organizationId, $orderId);

            if (!in_array($order->status, [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_PARTIAL], true)) {
                throw new \RuntimeException('Stock can only be reserved for CONFIRMED/PARTIAL orders.');
            }

            foreach ($order->lines as $line) {
                $remainingToReserve = (float) $line->quantity - (float) $line->reserved_quantity;
                if ($remainingToReserve <= 0) {
                    continue;
                }

                $ledger = StockLedger::query()
                    ->where('organization_id', $organizationId)
                    ->where('item_id', $line->item_id)
                    ->whereRaw('(quantity_available - quantity_reserved) > 0')
                    ->orderByDesc('quantity_available')
                    ->lockForUpdate()
                    ->first();

                if (!$ledger) {
                    throw new \RuntimeException("Insufficient stock for item {$line->item_id}.");
                }

                $free = (float) $ledger->quantity_available - (float) $ledger->quantity_reserved;
                if ($free < $remainingToReserve) {
                    throw new \RuntimeException("Insufficient stock for item {$line->item_id}.");
                }

                $this->inventoryPostingService->reserveStock(
                    itemId: $line->item_id,
                    warehouseId: $ledger->warehouse_id,
                    quantity: $remainingToReserve,
                    referenceType: 'SALES_ORDER',
                    referenceId: $order->id,
                    batchId: $ledger->batch_id,
                    organizationId: $organizationId
                );

                $line->reserved_quantity = (float) $line->reserved_quantity + $remainingToReserve;
                $line->reserved_warehouse_id = $ledger->warehouse_id;
                $line->reserved_batch_id = $ledger->batch_id;
                $line->save();
            }

            $order->updated_by = $userId;
            $order->save();

            return $order->refresh()->load('lines');
        });
    }

    public function cancelOrder(string $organizationId, string $userId, string $orderId): SalesOrder
    {
        return DB::transaction(function () use ($organizationId, $userId, $orderId) {
            $order = $this->findOrder($organizationId, $orderId);

            if (in_array($order->status, [SalesOrder::STATUS_COMPLETED, SalesOrder::STATUS_CANCELLED], true)) {
                throw new \RuntimeException('Completed/cancelled orders cannot be cancelled.');
            }

            foreach ($order->lines as $line) {
                if ((float) $line->reserved_quantity > 0 && $line->reserved_warehouse_id) {
                    $this->inventoryPostingService->releaseReservation(
                        itemId: $line->item_id,
                        warehouseId: $line->reserved_warehouse_id,
                        quantity: (float) $line->reserved_quantity,
                        batchId: $line->reserved_batch_id,
                        organizationId: $organizationId
                    );
                }

                $line->reserved_quantity = 0;
                $line->save();
            }

            $order->status = SalesOrder::STATUS_CANCELLED;
            $order->updated_by = $userId;
            $order->save();

            return $order->refresh()->load('lines');
        });
    }

    public function listDeliveryNotes(string $organizationId, int $perPage = 15)
    {
        return DeliveryNote::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate($perPage);
    }

    public function findDeliveryNote(string $organizationId, string $id): DeliveryNote
    {
        return DeliveryNote::query()
            ->with(['lines', 'salesOrder'])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);
    }

    public function createDeliveryNote(string $organizationId, string $userId, array $data): DeliveryNote
    {
        return DB::transaction(function () use ($organizationId, $userId, $data) {
            $order = $this->findOrder($organizationId, $data['sales_order_id']);

            if (!in_array($order->status, [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_PARTIAL], true)) {
                throw new \RuntimeException('Delivery note can only be created for CONFIRMED/PARTIAL orders.');
            }

            $note = DeliveryNote::create([
                'organization_id' => $organizationId,
                'dn_number' => $data['dn_number'] ?? $this->numberSeriesService->next(
                    $organizationId,
                    'DELIVERY_NOTE',
                    ['prefix' => 'DN-', 'padding' => 5]
                ),
                'sales_order_id' => $order->id,
                'warehouse_id' => $data['warehouse_id'],
                'delivery_date' => $data['delivery_date'],
                'status' => DeliveryNote::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($data['lines'] as $index => $line) {
                $orderLine = SalesOrderLine::query()
                    ->where('organization_id', $organizationId)
                    ->where('sales_order_id', $order->id)
                    ->findOrFail($line['sales_order_line_id']);

                DeliveryNoteLine::create([
                    'organization_id' => $organizationId,
                    'delivery_note_id' => $note->id,
                    'line_number' => $line['line_number'] ?? ($index + 1),
                    'sales_order_line_id' => $orderLine->id,
                    'item_id' => $orderLine->item_id,
                    'quantity' => $line['quantity'],
                    'uom_id' => $line['uom_id'] ?? $orderLine->uom_id,
                    'batch_id' => $line['batch_id'] ?? $orderLine->reserved_batch_id,
                ]);
            }

            return $note->load('lines');
        });
    }

    public function dispatchDeliveryNote(string $organizationId, string $userId, string $deliveryNoteId): DeliveryNote
    {
        return DB::transaction(function () use ($organizationId, $userId, $deliveryNoteId) {
            $note = $this->findDeliveryNote($organizationId, $deliveryNoteId);

            if ($note->status !== DeliveryNote::STATUS_DRAFT) {
                throw new \RuntimeException('Only DRAFT delivery notes can be dispatched.');
            }

            $order = $this->findOrder($organizationId, $note->sales_order_id);

            foreach ($note->lines as $line) {
                $orderLine = SalesOrderLine::query()
                    ->where('organization_id', $organizationId)
                    ->findOrFail($line->sales_order_line_id);

                $quantity = (float) $line->quantity;
                if ($quantity <= 0) {
                    throw new \RuntimeException('Dispatch quantity must be positive.');
                }

                if ((float) $orderLine->reserved_quantity + 0.0001 < $quantity) {
                    throw new \RuntimeException('Cannot dispatch more than reserved quantity.');
                }

                $ledger = StockLedger::query()
                    ->where('organization_id', $organizationId)
                    ->where('item_id', $line->item_id)
                    ->where('warehouse_id', $note->warehouse_id)
                    ->where('batch_id', $line->batch_id)
                    ->first();

                $this->inventoryPostingService->post(
                    transactionType: 'ISSUE',
                    itemId: $line->item_id,
                    warehouseId: $note->warehouse_id,
                    quantity: -$quantity,
                    unitCost: (float) ($ledger?->unit_cost ?? 0),
                    referenceType: 'DELIVERY_NOTE',
                    referenceId: $note->id,
                    batchId: $line->batch_id,
                    organizationId: $organizationId
                );

                if ($orderLine->reserved_warehouse_id) {
                    $this->inventoryPostingService->releaseReservation(
                        itemId: $orderLine->item_id,
                        warehouseId: $orderLine->reserved_warehouse_id,
                        quantity: $quantity,
                        batchId: $orderLine->reserved_batch_id,
                        organizationId: $organizationId
                    );
                }

                $orderLine->reserved_quantity = max(0, (float) $orderLine->reserved_quantity - $quantity);
                $orderLine->dispatched_quantity = (float) $orderLine->dispatched_quantity + $quantity;
                $orderLine->save();
            }

            $note->status = DeliveryNote::STATUS_DISPATCHED;
            $note->dispatched_at = now();
            $note->dispatched_by = $userId;
            $note->save();

            $this->syncOrderStatusFromDispatch($order);

            return $note->refresh()->load('lines');
        });
    }

    public function pendingDispatch(string $organizationId, int $perPage = 15)
    {
        return SalesOrder::query()
            ->with('lines')
            ->where('organization_id', $organizationId)
            ->whereIn('status', [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_PARTIAL])
            ->paginate($perPage);
    }

    private function syncOrderStatusFromDispatch(SalesOrder $order): void
    {
        $order->load('lines');

        $anyDispatched = false;
        $allDispatched = true;

        foreach ($order->lines as $line) {
            $qty = (float) $line->quantity;
            $dispatched = (float) $line->dispatched_quantity;
            if ($dispatched > 0) {
                $anyDispatched = true;
            }
            if ($dispatched + 0.0001 < $qty) {
                $allDispatched = false;
            }
        }

        if ($allDispatched && $anyDispatched) {
            $order->status = SalesOrder::STATUS_COMPLETED;
        } elseif ($anyDispatched) {
            $order->status = SalesOrder::STATUS_PARTIAL;
        }

        $order->save();
    }
}
