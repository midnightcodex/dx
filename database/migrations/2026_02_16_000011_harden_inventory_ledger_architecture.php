<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Harden inventory ledger architecture at DB layer.
     *
     * - Prevent stock transaction mutation (append-only with cancellation metadata update only)
     * - Restrict stock ledger writes to InventoryPostingService session context
     * - Fix null-batch uniqueness in stock_ledger
     * - Add defensive quantity constraints
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'chk_stock_transactions_non_zero_qty'
          AND connamespace = 'inventory'::regnamespace
    ) THEN
        ALTER TABLE inventory.stock_transactions
            ADD CONSTRAINT chk_stock_transactions_non_zero_qty
            CHECK (quantity <> 0);
    END IF;
END $$;
SQL);

        DB::unprepared(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'chk_stock_ledger_non_negative_reserved_transit'
          AND connamespace = 'inventory'::regnamespace
    ) THEN
        ALTER TABLE inventory.stock_ledger
            ADD CONSTRAINT chk_stock_ledger_non_negative_reserved_transit
            CHECK (quantity_reserved >= 0 AND quantity_in_transit >= 0);
    END IF;
END $$;
SQL);

        DB::statement('ALTER TABLE inventory.stock_ledger DROP CONSTRAINT IF EXISTS stock_ledger_unique');
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS ux_stock_ledger_org_item_wh_batch
            ON inventory.stock_ledger (
                organization_id,
                item_id,
                warehouse_id,
                COALESCE(batch_id, '00000000-0000-0000-0000-000000000000'::uuid)
            )
        ");

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION inventory.enforce_inventory_posting_context()
RETURNS TRIGGER AS $$
BEGIN
    IF current_setting('app.inventory_posting', true) IS DISTINCT FROM '1' THEN
        RAISE EXCEPTION 'Direct stock mutation blocked. Use InventoryPostingService.';
    END IF;

    IF TG_OP = 'DELETE' THEN
        RAISE EXCEPTION 'Stock ledger rows cannot be deleted directly.';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
SQL);

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION inventory.prevent_stock_transaction_mutation()
RETURNS TRIGGER AS $$
BEGIN
    IF current_setting('app.inventory_posting', true) IS DISTINCT FROM '1' THEN
        RAISE EXCEPTION 'Direct stock mutation blocked. Use InventoryPostingService.';
    END IF;

    IF TG_OP = 'DELETE' THEN
        RAISE EXCEPTION 'Stock transactions are immutable. Create a reversal instead.';
    END IF;

    IF TG_OP = 'UPDATE' THEN
        IF OLD.is_cancelled THEN
            RAISE EXCEPTION 'Cancelled stock transactions are immutable.';
        END IF;

        IF NEW.organization_id IS DISTINCT FROM OLD.organization_id
            OR NEW.transaction_type IS DISTINCT FROM OLD.transaction_type
            OR NEW.item_id IS DISTINCT FROM OLD.item_id
            OR NEW.warehouse_id IS DISTINCT FROM OLD.warehouse_id
            OR NEW.batch_id IS DISTINCT FROM OLD.batch_id
            OR NEW.quantity IS DISTINCT FROM OLD.quantity
            OR NEW.unit_cost IS DISTINCT FROM OLD.unit_cost
            OR NEW.total_value IS DISTINCT FROM OLD.total_value
            OR NEW.reference_type IS DISTINCT FROM OLD.reference_type
            OR NEW.reference_id IS DISTINCT FROM OLD.reference_id
            OR NEW.balance_after IS DISTINCT FROM OLD.balance_after
            OR NEW.created_by IS DISTINCT FROM OLD.created_by
            OR NEW.transaction_date IS DISTINCT FROM OLD.transaction_date
            OR NEW.created_at IS DISTINCT FROM OLD.created_at
        THEN
            RAISE EXCEPTION 'Stock transactions are immutable. Create a reversal instead.';
        END IF;

        IF NEW.is_cancelled IS DISTINCT FROM TRUE THEN
            RAISE EXCEPTION 'Transaction updates are restricted to cancellation metadata.';
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
SQL);

        DB::statement('DROP TRIGGER IF EXISTS trg_enforce_stock_ledger_context ON inventory.stock_ledger');
        DB::statement("
            CREATE TRIGGER trg_enforce_stock_ledger_context
            BEFORE INSERT OR UPDATE OR DELETE ON inventory.stock_ledger
            FOR EACH ROW
            EXECUTE FUNCTION inventory.enforce_inventory_posting_context()
        ");

        DB::statement('DROP TRIGGER IF EXISTS trg_prevent_stock_transaction_mutation ON inventory.stock_transactions');
        DB::statement("
            CREATE TRIGGER trg_prevent_stock_transaction_mutation
            BEFORE INSERT OR UPDATE OR DELETE ON inventory.stock_transactions
            FOR EACH ROW
            EXECUTE FUNCTION inventory.prevent_stock_transaction_mutation()
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS trg_prevent_stock_transaction_mutation ON inventory.stock_transactions');
        DB::statement('DROP TRIGGER IF EXISTS trg_enforce_stock_ledger_context ON inventory.stock_ledger');
        DB::statement('DROP FUNCTION IF EXISTS inventory.prevent_stock_transaction_mutation()');
        DB::statement('DROP FUNCTION IF EXISTS inventory.enforce_inventory_posting_context()');

        DB::statement('ALTER TABLE inventory.stock_transactions DROP CONSTRAINT IF EXISTS chk_stock_transactions_non_zero_qty');
        DB::statement('ALTER TABLE inventory.stock_ledger DROP CONSTRAINT IF EXISTS chk_stock_ledger_non_negative_reserved_transit');

        DB::statement('DROP INDEX IF EXISTS inventory.ux_stock_ledger_org_item_wh_batch');
        DB::statement("
            ALTER TABLE inventory.stock_ledger
            ADD CONSTRAINT stock_ledger_unique
            UNIQUE (organization_id, item_id, warehouse_id, batch_id)
        ");
    }
};
