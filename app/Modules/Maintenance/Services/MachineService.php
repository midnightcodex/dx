<?php

namespace App\Modules\Maintenance\Services;

use App\Core\Crud\CrudService;
use App\Modules\Maintenance\Models\BreakdownReport;
use App\Modules\Maintenance\Models\Machine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MachineService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(Machine::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): Machine
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): Machine
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(Machine $machine, string $userId, array $data): Machine
    {
        return $this->crud->update($machine, $userId, $data);
    }

    public function delete(Machine $machine): void
    {
        $this->crud->delete($machine);
    }

    public function history(string $organizationId, string $machineId): Collection
    {
        return BreakdownReport::query()
            ->where('organization_id', $organizationId)
            ->where('machine_id', $machineId)
            ->latest()
            ->get();
    }
}
