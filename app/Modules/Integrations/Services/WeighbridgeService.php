<?php

namespace App\Modules\Integrations\Services;

use App\Core\Crud\CrudService;
use App\Modules\Integrations\Models\WeighbridgeReading;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WeighbridgeService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(WeighbridgeReading::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): WeighbridgeReading
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): WeighbridgeReading
    {
        return $this->crud->create($organizationId, $userId, $data);
    }
}
