<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CatalogItem;

class CatalogItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $items = [
            // Parts
            ['name' => 'زيت محرك 5W-30', 'type' => 'part', 'unit_price' => 75.00, 'sku' => 'OIL-5W30-SYN'],
            ['name' => 'فلتر زيت', 'type' => 'part', 'unit_price' => 30.00, 'sku' => 'FILTER-OIL-001'],
            ['name' => 'فلتر هواء', 'type' => 'part', 'unit_price' => 55.00, 'sku' => 'FILTER-AIR-001'],
            ['name' => 'بطارية 70 أمبير', 'type' => 'part', 'unit_price' => 350.00, 'sku' => 'BAT-70AMP'],
            ['name' => 'مساعدات أمامية (زوج)', 'type' => 'part', 'unit_price' => 450.00, 'sku' => 'SHOCK-FR-PAIR'],
            ['name' => 'فحمات فرامل أمامية', 'type' => 'part', 'unit_price' => 220.00, 'sku' => 'PADS-BRK-FR'],

            // Services
            ['name' => 'خدمة تغيير زيت وفلتر', 'type' => 'service', 'unit_price' => 50.00, 'sku' => 'SVC-OIL-CHG'],
            ['name' => 'فحص كمبيوتر وتشخيص OBD2', 'type' => 'service', 'unit_price' => 150.00, 'sku' => 'SVC-DIAG-OBD2'],
            ['name' => 'ترصيص كفرات', 'type' => 'service', 'unit_price' => 80.00, 'sku' => 'SVC-WHEEL-BAL'],
            ['name' => 'وزن أذرعة', 'type' => 'service', 'unit_price' => 120.00, 'sku' => 'SVC-WHEEL-ALIGN'],
            ['name' => 'تركيب فحمات فرامل', 'type' => 'service', 'unit_price' => 100.00, 'sku' => 'SVC-BRK-INST'],
        ];

        foreach ($items as $item) {
            CatalogItem::create(array_merge($item, [
                'description' => 'وصف تجريبي لـ ' . $item['name'],
                'is_active' => true,
            ]));
        }
    }
}
