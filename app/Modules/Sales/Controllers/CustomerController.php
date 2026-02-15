<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Crud\CrudService;
use App\Modules\Sales\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private CrudService $service;

    public function __construct()
    {
        $this->service = new CrudService(Customer::class);
    }

    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;
        $paginated = $this->service->list($orgId, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Customers fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer = $this->service->create($request->user()->organization_id, $request->user()->id, $validated);
        return $this->success($customer, 'Customer created', 201);
    }

    public function show(Request $request, string $id)
    {
        $customer = $this->service->find($request->user()->organization_id, $id);
        return $this->success($customer, 'Customer retrieved');
    }

    public function update(Request $request, string $id)
    {
        $customer = $this->service->find($request->user()->organization_id, $id);
        $validated = $request->validate([
            'customer_code' => 'sometimes|string|max:50',
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer = $this->service->update($customer, $request->user()->id, $validated);
        return $this->success($customer, 'Customer updated');
    }

    public function destroy(Request $request, string $id)
    {
        $customer = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($customer);
        return $this->success(null, 'Customer deleted');
    }
}
