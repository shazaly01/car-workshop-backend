<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- تعريف الصلاحيات المفصلة ---

        // Dashboard
        Permission::create(['name' => 'view dashboard', 'guard_name' => 'api']);

        Permission::create(['name' => 'manage catalog', 'guard_name' => 'api']);
        // Work Orders (ممتازة كما هي)
        Permission::create(['name' => 'list work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'view work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'create work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'change work-order status', 'guard_name' => 'api']);

        // Diagnosis (ممتازة كما هي)
        Permission::create(['name' => 'add diagnosis', 'guard_name' => 'api']);

        // Clients (تفصيلية)
        Permission::create(['name' => 'list clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'view clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'create clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete clients', 'guard_name' => 'api']);

        // Vehicles (تفصيلية)
        Permission::create(['name' => 'list vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'view vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'create vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete vehicles', 'guard_name' => 'api']);

        // Financials (تفصيلية)
        Permission::create(['name' => 'create quotations', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit quotations', 'guard_name' => 'api']);
        Permission::create(['name' => 'view quotations', 'guard_name' => 'api']);
        Permission::create(['name' => 'create invoices', 'guard_name' => 'api']);
        Permission::create(['name' => 'view invoices', 'guard_name' => 'api']);
        Permission::create(['name' => 'void invoices', 'guard_name' => 'api']);
        Permission::create(['name' => 'create payments', 'guard_name' => 'api']);


        // --- تعريف الأدوار وربطها بالصلاحيات ---

        // 1. دور الفني (Technician)
        $technicianRole = Role::create(['name' => 'technician', 'guard_name' => 'api']);
        $technicianRole->givePermissionTo([
            'list work-orders',
            'view work-orders',
            'change work-order status',
            'add diagnosis',
        ]);

        // 2. دور موظف الاستقبال (Receptionist)
        $receptionistRole = Role::create(['name' => 'receptionist', 'guard_name' => 'api']);
        $receptionistRole->givePermissionTo([
            'view dashboard',
            // Clients
            'list clients', 'view clients', 'create clients', 'edit clients','delete clients',
            // Vehicles
            'list vehicles', 'view vehicles', 'create vehicles', 'edit vehicles','delete vehicles',
            // Work Orders
            'list work-orders', 'view work-orders', 'create work-orders', 'edit work-orders','delete work-orders',
            // Financials
            'create quotations','edit quotations', 'view quotations', 'create invoices', 'view invoices','void invoices', 'create payments',
            'change work-order status',
        ]);
        // لاحظ أننا لم نعط موظف الاستقبال صلاحية الحذف (delete) عن قصد كمثال

        // 3. دور المدير (Admin)
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        // المدير سيحصل على كل الصلاحيات عبر Gate::before، لا حاجة لربطها هنا.


        // --- إنشاء مستخدمين تجريبيين ---
        User::factory()->create([
            'name' => 'Admin User', 'username' => 'admin', 'email' => 'admin@example.com', 'password' => 'password',
        ])->assignRole($adminRole);

        User::factory()->create([
            'name' => 'Receptionist User', 'username' => 'reception', 'email' => 'reception@example.com', 'password' => 'password',
        ])->assignRole($receptionistRole);

        User::factory()->create([
            'name' => 'Technician User', 'username' => 'tech', 'email' => 'tech@example.com', 'password' => 'password',
        ])->assignRole($technicianRole);
    }
}
