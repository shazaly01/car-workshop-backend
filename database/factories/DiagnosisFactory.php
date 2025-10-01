<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Diagnosis>
 */
class DiagnosisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // --- العلاقات ---
            'work_order_id' => WorkOrder::factory(),
            'technician_id' => User::factory()->withRole('technician'),

            // --- الحقول الأخرى ---
            // إنشاء مصفوفة من أكواد OBD الوهمية
            'obd_codes' => $this->faker->randomElements(['P0300', 'P0171', 'C0035', 'B1881'], $this->faker->numberBetween(0, 3)),
            'manual_inspection_results' => $this->faker->paragraph(),
            'proposed_repairs' => $this->faker->sentence(),
        ];
    }
}
