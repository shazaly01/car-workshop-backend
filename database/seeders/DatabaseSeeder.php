<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

     $this->call([
            RolesAndPermissionsSeeder::class, // أولاً: لإنشاء الأدوار والمستخدمين
            CatalogItemSeeder::class,          // ثانياً: لإنشاء عناصر الكتالوج
            ClientsAndVehiclesSeeder::class,   // ثالثاً: لإنشاء العملاء ومركباتهم
            WorkOrderLifecycleSeeder::class,   // أخيراً: لمحاكاة دورة العمل الكاملة
        ]);
    }
}
