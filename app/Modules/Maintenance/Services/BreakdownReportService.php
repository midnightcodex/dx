<?php

namespace App\Modules\Maintenance\Services;

use App\Core\Crud\CrudService;
use App\Modules\Maintenance\Models\BreakdownReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BreakdownReportService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(BreakdownReport::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function openReports(string $organizationId): Collection
    {
        return BreakdownReport::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', ['REPORTED', 'ASSIGNED', 'IN_PROGRESS'])
            ->latest()
            ->get();
    }

    public function find(string $organizationId, string $id): BreakdownReport
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): BreakdownReport
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(BreakdownReport $report, string $userId, array $data): BreakdownReport
    {
        return $this->crud->update($report, $userId, $data);
    }

    public function delete(BreakdownReport $report): void
    {
        $this->crud->delete($report);
    }
}
