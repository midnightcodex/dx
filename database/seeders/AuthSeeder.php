<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or Get Default Organization
        $org = Organization::firstOrCreate(
            ['code' => 'DEMO'],
            [
                'name' => 'Demo Manufacturing Corp',
                'tax_id' => 'TAX-12345678',
                'currency' => 'USD',
                'timezone' => 'America/New_York',
                'is_active' => true,
            ]
        );

        $this->command->info("Organization checked/created: {$org->name}");

        // 2. Create Roles (using 'slug' not 'code')
        $roles = [
            'super-admin' => ['name' => 'Super Admin', 'description' => 'Full access to everything'],
            'admin' => ['name' => 'Admin', 'description' => 'Administrator access'],
            'manager' => ['name' => 'Manager', 'description' => 'Module manager access'],
            'operator' => ['name' => 'Operator', 'description' => 'Shop floor operator'],
            'viewer' => ['name' => 'Viewer', 'description' => 'Read-only access'],
        ];

        $createdRoles = [];
        foreach ($roles as $slug => $data) {
            $createdRoles[$slug] = Role::firstOrCreate(
                ['organization_id' => $org->id, 'slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_system' => true,
                ]
            );
        }
        $this->command->info('Roles checked/created: ' . implode(', ', array_keys($roles)));

        // 3. Create Permissions (using 'slug' not 'code', no organization_id)
        $modules = [
            'auth',
            'shared',
            'inventory',
            'manufacturing',
            'procurement',
            'sales',
            'maintenance',
            'hr',
            'compliance',
            'integrations',
            'reports',
        ];
        $actions = ['view', 'create', 'edit', 'delete', 'approve'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $slug = "{$module}-{$action}";
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => ucfirst($action) . ' ' . ucfirst($module),
                        'module' => $module,
                    ]
                );
            }
        }
        $this->command->info('Permissions checked/created for modules: ' . implode(', ', $modules));

        // 4. Create Super Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@examp.com'],
            [
                'organization_id' => $org->id,
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Assign super-admin role
        // Check if role already assigned to prevent duplicates
        $roleId = $createdRoles['super-admin']->id;
        $exists = DB::table('auth.user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', $roleId)
            ->exists();

        if (!$exists) {
            DB::table('auth.user_roles')->insert([
                'id' => (string) Str::uuid(), // Ensure it's a string
                'user_id' => $user->id,
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Assigned Super Admin role to user.");
        } else {
            $this->command->info("User already has Super Admin role.");
        }

        $this->command->info("Admin User ready: {$user->email} / password");
    }
}
