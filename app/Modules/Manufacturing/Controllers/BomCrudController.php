<?php

namespace App\Modules\Manufacturing\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manufacturing\Models\BomHeader;
use App\Modules\Manufacturing\Models\BomLine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BomCrudController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $boms = BomHeader::with('item')
            ->where('organization_id', $orgId)
            ->orderBy('bom_number')
            ->get();

        return $this->success($boms, 'BOMs fetched');
    }

    public function show(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;

        $bom = BomHeader::with('lines')
            ->where('organization_id', $orgId)
            ->findOrFail($id);

        return $this->success($bom, 'BOM retrieved');
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'item_id' => [
                'required',
                Rule::exists('inventory.items', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'bom_number' => 'required|string|max:50',
            'version' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'base_quantity' => 'required|numeric|min:0.0001',
            'uom_id' => [
                'nullable',
                Rule::exists('shared.uom', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'lines' => 'array',
            'lines.*.component_item_id' => [
                'required_with:lines',
                Rule::exists('inventory.items', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
            'lines.*.quantity_per_unit' => 'required_with:lines|numeric|min:0.000001',
        ]);

        $validated['organization_id'] = $orgId;
        $validated['created_by'] = $request->user()->id;

        $lines = $validated['lines'] ?? [];
        unset($validated['lines']);

        $bom = BomHeader::create($validated);

        foreach ($lines as $line) {
            BomLine::create([
                'organization_id' => $bom->organization_id,
                'bom_header_id' => $bom->id,
                'line_number' => $line['line_number'] ?? 1,
                'component_item_id' => $line['component_item_id'],
                'quantity_per_unit' => $line['quantity_per_unit'],
                'uom_id' => $line['uom_id'] ?? null,
                'scrap_percentage' => $line['scrap_percentage'] ?? 0,
                'operation_sequence' => $line['operation_sequence'] ?? null,
            ]);
        }

        return $this->success($bom->load('lines'), 'BOM created', 201);
    }

    public function update(Request $request, string $id)
    {
        $orgId = $request->user()->organization_id;

        $bom = BomHeader::where('organization_id', $orgId)->findOrFail($id);

        $validated = $request->validate([
            'bom_number' => 'sometimes|string|max:50',
            'version' => 'sometimes|integer|min:1',
            'is_active' => 'boolean',
            'base_quantity' => 'sometimes|numeric|min:0.0001',
            'uom_id' => [
                'nullable',
                Rule::exists('shared.uom', 'id')->where(fn($query) => $query->where('organization_id', $orgId)),
            ],
        ]);

        $validated['updated_by'] = $request->user()->id;
        $bom->update($validated);

        return $this->success($bom, 'BOM updated');
    }
}
