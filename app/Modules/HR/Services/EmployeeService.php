<?php

namespace App\Modules\HR\Services;

use App\Core\Crud\CrudService;
use App\Modules\HR\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService
{
    private CrudService $crud;

    public function __construct()
    {
        $this->crud = new CrudService(Employee::class);
    }

    public function list(string $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->crud->list($organizationId, $perPage);
    }

    public function find(string $organizationId, string $id): Employee
    {
        return $this->crud->find($organizationId, $id);
    }

    public function create(string $organizationId, string $userId, array $data): Employee
    {
        return $this->crud->create($organizationId, $userId, $data);
    }

    public function update(Employee $employee, string $userId, array $data): Employee
    {
        return $this->crud->update($employee, $userId, $data);
    }

    public function delete(Employee $employee): void
    {
        $this->crud->delete($employee);
    }
}
