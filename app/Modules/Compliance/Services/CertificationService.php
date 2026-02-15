<?php

namespace App\Modules\Compliance\Services;

use App\Core\Crud\CrudService;
use App\Modules\Compliance\Models\Certification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CertificationService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(Certification::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): Certification
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): Certification
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(Certification $cert, string $userId, array $data): Certification
    {
        return $this->crud->update($cert, $userId, $data);
    }

    public function delete(Certification $cert): void
    {
        $this->crud->delete($cert);
    }
}
