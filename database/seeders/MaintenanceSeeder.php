<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Maintenance\Models\Equipment;
use App\Modules\Maintenance\Models\MaintenanceTicket;
use App\Modules\Auth\Models\User;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $organizationId = 'org-0000-0000-0000-000000000001';
        $user = User::first();
        $userId = $user ? $user->id : null;

        if (!$userId)
            return;

        // 1. Equipment
        $machines = [
            [
                'name' => 'CNC Milling Machine X500',
                'code' => 'EQ-001',
                'status' => 'OPERATIONAL',
                'location' => 'Zone A',
                'manufacturer' => 'Haas',
            ],
            [
                'name' => 'Industrial Lathe L200',
                'code' => 'EQ-002',
                'status' => 'DOWN', // Actually broken
                'location' => 'Zone A',
                'manufacturer' => 'Mazak',
            ],
            [
                'name' => 'Forklift 3-Ton',
                'code' => 'EQ-003',
                'status' => 'OPERATIONAL',
                'location' => 'Warehouse',
                'manufacturer' => 'Toyota',
            ],
            [
                'name' => 'Air Compressor',
                'code' => 'EQ-004',
                'status' => 'MAINTENANCE', // Scheduled
                'location' => 'Utility Room',
                'manufacturer' => 'Atlas Copco',
            ]
        ];

        foreach ($machines as $mach) {
            Equipment::firstOrCreate(
                ['code' => $mach['code']],
                array_merge($mach, [
                    'organization_id' => $organizationId,
                    'created_by' => $userId,
                    'purchase_date' => now()->subYear(),
                    'last_maintenance_date' => now()->subMonth(),
                    'next_maintenance_date' => now()->addMonth(),
                ])
            );
        }

        // 2. Tickets
        $lathe = Equipment::where('code', 'EQ-002')->first();
        if ($lathe) {
            MaintenanceTicket::firstOrCreate(
                ['ticket_number' => 'MT-202602-001'],
                [
                    'organization_id' => $organizationId,
                    'equipment_id' => $lathe->id,
                    'reported_by' => $userId,
                    'subject' => 'Strange noise from spindle',
                    'description' => 'Machine making grinding noise when operating at high RPM.',
                    'priority' => 'HIGH',
                    'status' => 'OPEN',
                    'created_by' => $userId,
                ]
            );
        }
    }
}
