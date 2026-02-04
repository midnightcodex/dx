<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\Auth\Models\User;
use App\Modules\Maintenance\Models\Equipment;
use App\Modules\Maintenance\Models\MaintenanceTicket;

class MaintenanceFlowTest extends TestCase
{
    public function test_maintenance_ticket_resolution()
    {
        // 1. Setup
        $user = User::where('email', 'admin@examp.com')->first();
        $this->actingAs($user);

        // 2. Create Equipment (broken)
        $eqCode = 'TEST-EQ-' . rand(100, 999);
        $this->post(route('maintenance.equipment.store'), [
            'name' => 'Test Broken Machine',
            'code' => $eqCode,
            'status' => 'DOWN',
            'location' => 'Test Zone'
        ]);

        $eq = Equipment::where('code', $eqCode)->firstOrFail();
        $this->assertEquals('DOWN', $eq->status);

        // 3. Create Ticket
        $response = $this->post(route('maintenance.tickets.store'), [
            'equipment_id' => $eq->id,
            'subject' => 'Won\'t start',
            'description' => 'Power button stuck',
            'priority' => 'HIGH'
        ]);
        $response->assertRedirect();

        $ticket = MaintenanceTicket::where('equipment_id', $eq->id)->firstOrFail();
        $this->assertEquals('OPEN', $ticket->status);

        // 4. Resolve Ticket
        $this->post(route('maintenance.tickets.resolve', $ticket->id), [
            'resolution_notes' => 'Replaced button'
        ]);

        // 5. Verify Outcome
        $ticket->refresh();
        $eq->refresh();

        $this->assertEquals('CLOSED', $ticket->status);
        $this->assertEquals('OPERATIONAL', $eq->status, 'Equipment should be operational after ticket resolution');
    }
}
