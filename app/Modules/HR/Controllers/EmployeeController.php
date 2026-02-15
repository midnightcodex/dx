<?php

namespace App\Modules\HR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Requests\StoreEmployeeRequest;
use App\Modules\HR\Requests\UpdateEmployeeRequest;
use App\Modules\HR\Resources\EmployeeResource;
use App\Modules\HR\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private EmployeeService $service;

    public function __construct()
    {
        $this->service = new EmployeeService();
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success(
            EmployeeResource::collection($paginated->items()),
            'Employees fetched',
            200,
            $this->paginationMeta($paginated)
        );
    }

    public function store(StoreEmployeeRequest $request)
    {
        $employee = $this->service->create(
            $request->user()->organization_id,
            $request->user()->id,
            $request->validated()
        );
        return $this->success(new EmployeeResource($employee), 'Employee created', 201);
    }

    public function show(Request $request, string $id)
    {
        $employee = $this->service->find($request->user()->organization_id, $id);
        return $this->success(new EmployeeResource($employee), 'Employee retrieved');
    }

    public function update(UpdateEmployeeRequest $request, string $id)
    {
        $employee = $this->service->find($request->user()->organization_id, $id);
        $employee = $this->service->update($employee, $request->user()->id, $request->validated());
        return $this->success(new EmployeeResource($employee), 'Employee updated');
    }

    public function destroy(Request $request, string $id)
    {
        $employee = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($employee);
        return $this->success(null, 'Employee deleted');
    }
}
