<?php

namespace App\Modules\HR\Services;

use App\Core\Crud\CrudService;
use App\Modules\HR\Models\Shift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShiftService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(Shift::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): Shift
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): Shift
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(Shift $shift, string $userId, array $data): Shift
    {
        return $this->crud->update($shift, $userId, $data);
    }

    public function delete(Shift $shift): void
    {
        $this->crud->delete($shift);
    }
}
