<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkOrder>
 */
class WorkOrderFactory extends Factory
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
            // سيتم التعامل معها في دالة configure لضمان التناسق
            'client_id' => Client::factory(),
            'vehicle_id' => Vehicle::factory(),
            'created_by_user_id' => User::factory()->withRole('receptionist'), // افتراضيًا، موظف الاستقبال هو من ينشئه

            // --- الحقول الأخرى ---
            'number' => 'WO-' . date('Y') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'client_complaint' => $this->faker->paragraph(),
            'initial_inspection_notes' => $this->faker->optional()->paragraph(), // قد يكون فارغًا
            'status' => 'pending_diagnosis', // الحالة الأولية دائمًا
        ];
    }

    /**
     * --- الجزء الجديد والمهم ---
     *
     * تكوين الـ Factory لضمان أن السيارة تنتمي دائمًا إلى العميل.
     */
    public function configure(): static
    {
        return $this->afterMaking(function ($workOrder) {
            // إذا تم إنشاء أمر العمل مع عميل ولكن بدون سيارة،
            // قم بإنشاء سيارة جديدة لهذا العميل المحدد.
            if ($workOrder->client_id && !$workOrder->vehicle_id) {
                $workOrder->vehicle_id = Vehicle::factory()->create(['client_id' => $workOrder->client_id])->id;
            }
        })->afterCreating(function ($workOrder) {
            // بعد إنشاء أمر العمل، تأكد من أن السيارة مرتبطة بالعميل الصحيح.
            // هذا يحل مشكلة إذا تم إنشاء العميل والسيارة بشكل مستقل.
            $vehicle = Vehicle::find($workOrder->vehicle_id);
            if ($vehicle->client_id !== $workOrder->client_id) {
                $vehicle->client_id = $workOrder->client_id;
                $vehicle->save();
            }
        });
    }
}
