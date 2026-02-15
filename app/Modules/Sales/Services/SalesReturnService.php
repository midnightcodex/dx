<?php

namespace App\Modules\Sales\Services;

use App\Core\Crud\CrudService;
use App\Modules\Sales\Models\SalesReturn;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SalesReturnService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(SalesReturn::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): SalesReturn
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): SalesReturn
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(SalesReturn $return, string $userId, array $data): SalesReturn
    {
        return $this->crud->update($return, $userId, $data);
    }

    public function delete(SalesReturn $return): void
    {
        $this->crud->delete($return);
    }
}
