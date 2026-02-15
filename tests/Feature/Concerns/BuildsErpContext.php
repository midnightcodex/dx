<?php

namespace Tests\Feature\Concerns;

use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Models\User;
use App\Modules\Inventory\Models\Item;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Procurement\Models\Vendor;
use App\Modules\Sales\Models\Customer;
use App\Modules\Shared\Models\Organization;
use App\Modules\Shared\Models\Uom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait BuildsErpContext
{
    protected function actingAsJwt(User $user): void
    {
        $this->actingAs($user, 'jwt');
    }

    protected function createOrganization(array $overrides = []): Organization
    {
        return Organization::create(array_merge([
            'name' => 'Org ' . Str::upper(Str::random(6)),
            'code' => 'ORG-' . Str::upper(Str::random(6)),
            'currency' => 'USD',
            'timezone' => 'UTC',
            'is_active' => true,
        ], $overrides));
    }

    protected function createSuperAdminUser(Organization $organization, array $overrides = []): User
    {
        $user = $this->createUser($organization, $overrides);

        $role = Role::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'slug' => 'super-admin',
            ],
            [
                'name' => 'Super Admin',
                'description' => 'System super admin',
                'is_system' => true,
            ]
        );

        $this->attachUserRole($user, $role);

        return $user;
    }

    protected function createScopedUserWithPermissions(
        Organization $organization,
        string $roleSlug,
        array $permissionSlugs,
        array $userOverrides = []
    ): User {
        $user = $this->createUser($organization, $userOverrides);

        $role = Role::create([
            'organization_id' => $organization->id,
            'name' => Str::headline(str_replace('-', ' ', $roleSlug)),
            'slug' => $roleSlug,
            'is_system' => false,
        ]);

        foreach ($permissionSlugs as $permissionSlug) {
            $module = explode('-', $permissionSlug)[0] ?? 'shared';
            $permission = Permission::firstOrCreate(
                ['slug' => $permissionSlug],
                [
                    'name' => Str::headline(str_replace('-', ' ', $permissionSlug)),
                    'module' => $module,
                ]
            );

            DB::table('auth.role_permissions')->insert([
                'id' => (string) Str::uuid(),
                'role_id' => $role->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->attachUserRole($user, $role);

        return $user;
    }

    protected function createUom(Organization $organization, array $overrides = []): Uom
    {
        return Uom::create(array_merge([
            'organization_id' => $organization->id,
            'name' => 'Piece',
            'symbol' => 'PCS-' . Str::upper(Str::random(4)),
            'category' => 'quantity',
            'conversion_factor' => 1,
            'is_active' => true,
        ], $overrides));
    }

    protected function createWarehouse(Organization $organization, array $overrides = []): Warehouse
    {
        return Warehouse::create(array_merge([
            'organization_id' => $organization->id,
            'name' => 'Warehouse ' . Str::upper(Str::random(4)),
            'code' => 'WH-' . Str::upper(Str::random(5)),
            'type' => 'WAREHOUSE',
            'allow_negative_stock' => false,
            'is_active' => true,
        ], $overrides));
    }

    protected function createItem(Organization $organization, Uom $uom, array $overrides = []): Item
    {
        return Item::create(array_merge([
            'organization_id' => $organization->id,
            'item_code' => 'ITM-' . Str::upper(Str::random(6)),
            'name' => 'Item ' . Str::upper(Str::random(5)),
            'primary_uom_id' => $uom->id,
            'item_type' => 'STOCKABLE',
            'stock_type' => 'RAW_MATERIAL',
            'is_batch_tracked' => false,
            'is_serial_tracked' => false,
            'is_active' => true,
        ], $overrides));
    }

    protected function createVendor(Organization $organization, array $overrides = []): Vendor
    {
        return Vendor::create(array_merge([
            'organization_id' => $organization->id,
            'vendor_code' => 'VND-' . Str::upper(Str::random(5)),
            'name' => 'Vendor ' . Str::upper(Str::random(4)),
            'status' => 'ACTIVE',
            'currency' => 'USD',
        ], $overrides));
    }

    protected function createCustomer(Organization $organization, array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'organization_id' => $organization->id,
            'customer_code' => 'CUST-' . Str::upper(Str::random(5)),
            'name' => 'Customer ' . Str::upper(Str::random(4)),
            'is_active' => true,
        ], $overrides));
    }

    private function createUser(Organization $organization, array $overrides = []): User
    {
        return User::create(array_merge([
            'organization_id' => $organization->id,
            'name' => 'User ' . Str::upper(Str::random(4)),
            'email' => Str::lower(Str::random(10)) . '@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'token_version' => 1,
        ], $overrides));
    }

    private function attachUserRole(User $user, Role $role): void
    {
        DB::table('auth.user_roles')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'role_id' => $role->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
