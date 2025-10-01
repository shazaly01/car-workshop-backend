<?php

namespace Database\Factories;

use App\Models\Client; // <-- استيراد نموذج العميل
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str; // <-- استيراد Str لتوليد أرقام عشوائية

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // --- العلاقة مع العميل ---
            // إذا لم يتم توفير client_id، قم بإنشاء عميل جديد تلقائيًا
            'client_id' => Client::factory(),

            // 'vin' => رقم هيكل فريد مكون من 17 حرفًا
            'vin' => $this->faker->unique()->bothify('?#?#?#?#?#?#?#?##'),

            // 'plate_number' => رقم لوحة فريد
            'plate_number' => $this->faker->unique()->bothify('???-####'),

            // 'make' => 'Toyota', 'Ford', etc.
            'make' => $this->faker->randomElement(['Toyota', 'Ford', 'Honda', 'BMW', 'Mercedes-Benz', 'Nissan']),

            // 'model' => 'Camry', 'Focus', etc.
            'model' => $this->faker->word(),

            // 'year' => '2015'
            'year' => $this->faker->numberBetween(2000, date('Y')),

            // 'color' => 'Red', 'Blue', etc.
            'color' => $this->faker->safeColorName(),

            // 'mileage' => 150000
            'mileage' => $this->faker->numberBetween(10000, 250000),
        ];
    }
}
