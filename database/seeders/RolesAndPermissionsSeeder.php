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
        // إعادة تعيين الكاش الخاص بالصلاحيات
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- تعريف الصلاحيات باستخدام firstOrCreate لمنع الأخطاء ---

        // Users
        Permission::firstOrCreate(['name' => 'list users', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create users', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit users', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete users', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'assign roles', 'guard_name' => 'api']);

        // Roles
        Permission::firstOrCreate(['name' => 'list roles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view roles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create roles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit roles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete roles', 'guard_name' => 'api']);

        // Dashboard
        Permission::firstOrCreate(['name' => 'view dashboard', 'guard_name' => 'api']);

        // Catalog
        Permission::firstOrCreate(['name' => 'manage catalog', 'guard_name' => 'api']);

        // Work Orders
        Permission::firstOrCreate(['name' => 'list work-orders', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view work-orders', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create work-orders', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit work-orders', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete work-orders', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'change work-order status', 'guard_name' => 'api']);

        // Diagnosis
        Permission::firstOrCreate(['name' => 'add diagnosis', 'guard_name' => 'api']);

        // Clients
        Permission::firstOrCreate(['name' => 'list clients', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view clients', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create clients', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit clients', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete clients', 'guard_name' => 'api']);

        // Vehicles
        Permission::firstOrCreate(['name' => 'list vehicles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view vehicles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create vehicles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit vehicles', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete vehicles', 'guard_name' => 'api']);

        // Financials
        Permission::firstOrCreate(['name' => 'create quotations', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit quotations', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view quotations', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create invoices', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view invoices', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'void invoices', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create payments', 'guard_name' => 'api']);

        // Reports
        Permission::firstOrCreate(['name' => 'view reports', 'guard_name' => 'api']);


        // --- تعريف الأدوار وربطها بالصلاحيات ---

        // 1. دور الفني (Technician)
        $technicianRole = Role::firstOrCreate(['name' => 'technician', 'guard_name' => 'api']);
        $technicianRole->syncPermissions([
            'list work-orders',
            'view work-orders',
            'change work-order status',
            'add diagnosis',
        ]);

        // 2. دور موظف الاستقبال (Receptionist)
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'api']);
        $receptionistRole->syncPermissions([
            'view dashboard',
            'list clients', 'view clients', 'create clients', 'edit clients','delete clients',
            'list vehicles', 'view vehicles', 'create vehicles', 'edit vehicles','delete vehicles',
            'list work-orders', 'view work-orders', 'create work-orders', 'edit work-orders','delete work-orders',
            'create quotations','edit quotations', 'view quotations', 'create invoices', 'view invoices','void invoices', 'create payments',
            'change work-order status','assign roles',
        ]);

        // 3. دور المدير (Admin)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->syncPermissions(Permission::all());

        // --- إنشاء مستخدمين تجريبيين ---
        User::factory()->withRole('admin')->create([
            'name' => 'Admin User', 'username' => 'admin', 'email' => 'admin@example.com', 'password' => 'password',
        ]);

        User::factory()->withRole('receptionist')->create([
            'name' => 'Receptionist User', 'username' => 'reception', 'email' => 'reception@example.com', 'password' => 'password',
        ]);

        User::factory()->withRole('technician')->create([
            'name' => 'Technician User', 'username' => 'tech', 'email' => 'tech@example.com', 'password' => 'password',
        ]);
    }
}
