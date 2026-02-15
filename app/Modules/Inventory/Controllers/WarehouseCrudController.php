<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseCrudController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $warehouses = Warehouse::where('organization_id', $orgId)
            ->orderBy('name')
            ->get();

        return $this->success($warehouses, 'Warehouses fetched');
    }

    public function show(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;

        $warehouse = Warehouse::where('organization_id', $orgId)
            ->findOrFail($id);

        return $this->success($warehouse, 'Warehouse retrieved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'type' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'allow_negative_stock' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['organization_id'] = $request->user()->organization_id;
        $warehouse = Warehouse::create($validated);

        return $this->success($warehouse, 'Warehouse created', 201);
    }

    public function update(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;

        $warehouse = Warehouse::where('organization_id', $orgId)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'code' => 'sometimes|string|max:50',
            'type' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'allow_negative_stock' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return $this->success($warehouse, 'Warehouse updated');
    }
}
