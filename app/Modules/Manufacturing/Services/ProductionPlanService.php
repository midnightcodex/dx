<?php

namespace App\Modules\Manufacturing\Services;

use App\Core\Crud\CrudService;
use App\Modules\Manufacturing\Models\ProductionPlan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductionPlanService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(ProductionPlan::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): ProductionPlan
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): ProductionPlan
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(ProductionPlan $plan, string $userId, array $data): ProductionPlan
    {
        return $this->crud->update($plan, $userId, $data);
    }

    public function delete(ProductionPlan $plan): void
    {
        $this->crud->delete($plan);
    }
}
