<?php

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Core\Crud\CrudService;
use App\Modules\Procurement\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    private CrudService $service;

    public function __construct()
    {
        $this->service = new CrudService(Vendor::class);
    }

    public function index(Request $request)
    {
        $paginated = $this->service->list($request->user()->organization_id, (int) $request->input('per_page', 15));
        return $this->success($paginated->items(), 'Vendors fetched', 200, $this->paginationMeta($paginated));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $vendor = $this->service->create($request->user()->organization_id, $request->user()->id, $validated);
        return $this->success($vendor, 'Vendor created', 201);
    }

    public function show(Request $request, string $id)
    {
        $vendor = $this->service->find($request->user()->organization_id, $id);
        return $this->success($vendor, 'Vendor retrieved');
    }

    public function update(Request $request, string $id)
    {
        $vendor = $this->service->find($request->user()->organization_id, $id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $vendor = $this->service->update($vendor, $request->user()->id, $validated);
        return $this->success($vendor, 'Vendor updated');
    }

    public function destroy(Request $request, string $id)
    {
        $vendor = $this->service->find($request->user()->organization_id, $id);
        $this->service->delete($vendor);
        return $this->success(null, 'Vendor deleted');
    }
}
