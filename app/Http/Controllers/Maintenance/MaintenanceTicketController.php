<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Models\Equipment;
use App\Modules\Maintenance\Models\MaintenanceTicket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaintenanceTicketController extends Controller
{
    public function index()
    {
        $tickets = MaintenanceTicket::with(['equipment', 'reporter', 'assignee'])
            ->latest()
            ->paginate(15);

        $equipmentList = Equipment::all(['id', 'name', 'code']);

        return Inertia::render('Maintenance/Index', [
            'tickets' => $tickets,
            'equipmentList' => $equipmentList
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:maintenance.equipment,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:LOW,NORMAL,HIGH,CRITICAL',
        ]);

        // Auto-set status to DOWN if priority is critical
        if ($validated['priority'] === 'CRITICAL') {
            Equipment::where('id', $validated['equipment_id'])->update(['status' => 'DOWN']);
        }

        MaintenanceTicket::create(array_merge($validated, [
            'organization_id' => auth()->user()->organization_id,
            'ticket_number' => 'MT-' . date('Ymd') . '-' . rand(100, 999),
            'reported_by' => auth()->id(),
            'status' => 'OPEN',
            'created_by' => auth()->id(),
        ]));

        return back()->with('message', 'Ticket created.');
    }

    public function resolve(Request $request, $id)
    {
        $request->validate(['resolution_notes' => 'required|string']);

        $ticket = MaintenanceTicket::findOrFail($id);
        $ticket->update([
            'status' => 'CLOSED',
            'completed_at' => now(),
            'resolution_notes' => $request->resolution_notes,
        ]);

        // If equipment was down, bring it up
        $ticket->equipment->update(['status' => 'OPERATIONAL']);

        return back()->with('message', 'Ticket resolved and closed.');
    }
}
