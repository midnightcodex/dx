<?php

namespace App\Modules\Integrations\Services;

use App\Core\Crud\CrudService;
use App\Modules\Integrations\Models\AccountingExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountingExportService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(AccountingExport::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): AccountingExport
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): AccountingExport
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function runInvoiceExport(string $organizationId, string $userId, array $data): AccountingExport
    {
        $export = $this->create($organizationId, $userId, array_merge($data, [
            'status' => 'PENDING',
            'export_type' => 'PURCHASE_INVOICE',
            'export_date' => now()->toDateString(),
        ]));

        try {
            $rows = DB::table('procurement.purchase_invoices')
                ->where('organization_id', $organizationId)
                ->when(!empty($data['from_date']), fn($q) => $q->whereDate('invoice_date', '>=', $data['from_date']))
                ->when(!empty($data['to_date']), fn($q) => $q->whereDate('invoice_date', '<=', $data['to_date']))
                ->get()
                ->map(fn($r) => (array) $r)
                ->all();

            $path = $this->writeExportFile($export->id, $rows, $data['file_format'] ?? 'JSON');
            $export->status = 'EXPORTED';
            $export->file_path = $path;
            $export->file_format = strtoupper($data['file_format'] ?? 'JSON');
            $export->exported_at = now();
            $export->exported_by = $userId;
            $export->reference_ids = array_map(fn($r) => $r['id'] ?? null, $rows);
            $export->save();
        } catch (\Throwable $e) {
            $export->status = 'FAILED';
            $export->error_message = $e->getMessage();
            $export->save();
            throw $e;
        }

        return $export->refresh();
    }

    public function runStockValuationExport(string $organizationId, string $userId, array $data): AccountingExport
    {
        $export = $this->create($organizationId, $userId, array_merge($data, [
            'status' => 'PENDING',
            'export_type' => 'STOCK_VALUATION',
            'export_date' => now()->toDateString(),
        ]));

        try {
            $rows = DB::table('inventory.stock_ledger')
                ->where('organization_id', $organizationId)
                ->selectRaw('item_id, warehouse_id, batch_id, quantity_available, unit_cost, (quantity_available * unit_cost) as total_value')
                ->get()
                ->map(fn($r) => (array) $r)
                ->all();

            $path = $this->writeExportFile($export->id, $rows, $data['file_format'] ?? 'JSON');
            $export->status = 'EXPORTED';
            $export->file_path = $path;
            $export->file_format = strtoupper($data['file_format'] ?? 'JSON');
            $export->exported_at = now();
            $export->exported_by = $userId;
            $export->save();
        } catch (\Throwable $e) {
            $export->status = 'FAILED';
            $export->error_message = $e->getMessage();
            $export->save();
            throw $e;
        }

        return $export->refresh();
    }

    private function writeExportFile(string $exportId, array $rows, string $format): string
    {
        $fmt = strtoupper($format);
        $dir = 'exports';
        $fileName = "{$dir}/{$exportId}." . strtolower($fmt === 'CSV' ? 'csv' : 'json');

        if ($fmt === 'CSV') {
            $csv = $this->toCsv($rows);
            Storage::disk('local')->put($fileName, $csv);
        } else {
            Storage::disk('local')->put($fileName, json_encode($rows, JSON_PRETTY_PRINT));
        }

        return $fileName;
    }

    private function toCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $headers = array_keys($rows[0]);
        $lines = [implode(',', $headers)];
        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(function ($value) {
                $value = (string) $value;
                $value = str_replace('"', '""', $value);
                return '"' . $value . '"';
            }, array_values($row)));
        }

        return implode("\n", $lines);
    }
}
