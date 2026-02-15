<?php

namespace App\Modules\Reports\Services;

use App\Core\Crud\CrudService;
use App\Modules\Reports\Models\ReportDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(ReportDefinition::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): ReportDefinition
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): ReportDefinition
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(ReportDefinition $definition, string $userId, array $data): ReportDefinition
    {
        return $this->crud->update($definition, $userId, $data);
    }

    public function delete(ReportDefinition $definition): void
    {
        $this->crud->delete($definition);
    }
}
