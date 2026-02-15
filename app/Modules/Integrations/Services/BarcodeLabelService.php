<?php

namespace App\Modules\Integrations\Services;

use App\Core\Crud\CrudService;
use App\Modules\Integrations\Models\BarcodeLabel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BarcodeLabelService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(BarcodeLabel::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): BarcodeLabel
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): BarcodeLabel
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function findByBarcode(string $organizationId, string $barcode): BarcodeLabel
    {
        return BarcodeLabel::query()
            ->where('organization_id', $organizationId)
            ->where('barcode', $barcode)
            ->firstOrFail();
    }

    public function findManyByIds(string $organizationId, array $ids): Collection
    {
        return BarcodeLabel::query()
            ->where('organization_id', $organizationId)
            ->whereIn('id', $ids)
            ->get();
    }
}
