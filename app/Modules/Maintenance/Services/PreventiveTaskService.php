<?php

namespace App\Modules\Maintenance\Services;

use App\Core\Crud\CrudService;
use App\Modules\Maintenance\Models\PreventiveTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PreventiveTaskService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(PreventiveTask::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function dueTasks(string $organizationId): Collection
    {
        return PreventiveTask::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', ['SCHEDULED', 'IN_PROGRESS', 'OVERDUE', null])
            ->whereDate('scheduled_date', '<=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->get();
    }

    public function find(string $organizationId, string $id): PreventiveTask
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): PreventiveTask
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(PreventiveTask $task, string $userId, array $data): PreventiveTask
    {
        return $this->crud->update($task, $userId, $data);
    }

    public function delete(PreventiveTask $task): void
    {
        $this->crud->delete($task);
    }
}
