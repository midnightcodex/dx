<?php

namespace App\Modules\Maintenance\Services;

use App\Core\Crud\CrudService;
use App\Modules\Maintenance\Models\PreventiveSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PreventiveScheduleService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(PreventiveSchedule::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): PreventiveSchedule
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): PreventiveSchedule
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(PreventiveSchedule $schedule, string $userId, array $data): PreventiveSchedule
    {
        return $this->crud->update($schedule, $userId, $data);
    }

    public function delete(PreventiveSchedule $schedule): void
    {
        $this->crud->delete($schedule);
    }
}
