<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Models\Equipment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::query();

        if ($request->has('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%')
                ->orWhere('code', 'ilike', '%' . $request->search . '%');
        }

        $equipment = $query->latest()->paginate(15);

        return Inertia::render('Maintenance/Equipment', [
            'equipment' => $equipment
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:maintenance.equipment,code',
            'status' => 'required|in:OPERATIONAL,DOWN,MAINTENANCE',
            'location' => 'nullable|string',
            'manufacturer' => 'nullable|string',
        ]);

        Equipment::create(array_merge($validated, [
            'organization_id' => auth()->user()->organization_id,
            'created_by' => auth()->id(),
        ]));

        return back()->with('message', 'Equipment registered successfully.');
    }
}
