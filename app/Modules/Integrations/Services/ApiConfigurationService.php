<?php

namespace App\Modules\Integrations\Services;

use App\Core\Crud\CrudService;
use App\Modules\Integrations\Models\ApiConfiguration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiConfigurationService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(ApiConfiguration::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): ApiConfiguration
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): ApiConfiguration
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(ApiConfiguration $config, string $userId, array $data): ApiConfiguration
    {
        return $this->crud->update($config, $userId, $data);
    }

    public function delete(ApiConfiguration $config): void
    {
        $this->crud->delete($config);
    }
}
