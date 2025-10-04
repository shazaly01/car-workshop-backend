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

        Permission::create(['name' => 'list users', 'guard_name' => 'api']); // تم تغييرها من view users للتوحيد
        Permission::create(['name' => 'create users', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit users', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete users', 'guard_name' => 'api']);
        Permission::create(['name' => 'assign roles', 'guard_name' => 'api']);


         Permission::create(['name' => 'list roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'view roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'create roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit roles', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete roles', 'guard_name' => 'api']);
        // Dashboard
        Permission::create(['name' => 'view dashboard', 'guard_name' => 'api']);

        Permission::create(['name' => 'manage catalog', 'guard_name' => 'api']);
        // Work Orders
        Permission::create(['name' => 'list work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'view work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'create work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete work-orders', 'guard_name' => 'api']);
        Permission::create(['name' => 'change work-order status', 'guard_name' => 'api']);

        // Diagnosis
        Permission::create(['name' => 'add diagnosis', 'guard_name' => 'api']);

        // Clients
        Permission::create(['name' => 'list clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'view clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'create clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit clients', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete clients', 'guard_name' => 'api']);

        // Vehicles
        Permission::create(['name' => 'list vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'view vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'create vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit vehicles', 'guard_name' => 'api']);
        Permission::create(['name' => 'delete vehicles', 'guard_name' => 'api']);

        // Financials
        Permission::create(['name' => 'create quotations', 'guard_name' => 'api']);
        Permission::create(['name' => 'edit quotations', 'guard_name' => 'api']);
        Permission::create(['name' => 'view quotations', 'guard_name' => 'api']);
        Permission::create(['name' => 'create invoices', 'guard_name' => 'api']);
        Permission::create(['name' => 'view invoices', 'guard_name' => 'api']);
        Permission::create(['name' => 'void invoices', 'guard_name' => 'api']);
        Permission::create(['name' => 'create payments', 'guard_name' => 'api']);

        // *** START: NEW PERMISSIONS ***
        // Users
        Permission::create(['name' => 'view users', 'guard_name' => 'api']);

        // Reports
        Permission::create(['name' => 'view reports', 'guard_name' => 'api']);
        // *** END: NEW PERMISSIONS ***


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
            'change work-order status','assign roles',
        ]);

        // 3. دور المدير (Admin)
       $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        // هذا السطر يضمن أن المدير يحصل على الصلاحيات الجديدة تلقائياً
        $adminRole->givePermissionTo(Permission::all());

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
