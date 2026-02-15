<?php

namespace App\Modules\Procurement\Services;

use App\Core\Crud\CrudService;
use App\Modules\Procurement\Models\PurchaseInvoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PurchaseInvoiceService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(PurchaseInvoice::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): PurchaseInvoice
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): PurchaseInvoice
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(PurchaseInvoice $invoice, string $userId, array $data): PurchaseInvoice
    {
        return $this->crud->update($invoice, $userId, $data);
    }

    public function delete(PurchaseInvoice $invoice): void
    {
        $this->crud->delete($invoice);
    }
}
