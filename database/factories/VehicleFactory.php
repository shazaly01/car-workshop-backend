<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

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
        $fakerAr = FakerFactory::create('ar_SA');

        return [
            'vin' => $this->faker->unique()->bothify('?#?#?#?#?#?#?#?#?'),
            'plate_number' => $fakerAr->unique()->bothify('### ####'), // صيغة لوحة عربية
            'make' => $this->faker->randomElement(['تويوتا', 'فورد', 'هوندا', 'هيونداي', 'نيسان']),
            'model' => $this->faker->randomElement(['كامري', 'أكسنت', 'أكورد', 'إلنترا']),
            'year' => $this->faker->numberBetween(2010, 2024),
            'color' => $fakerAr->colorName(),
            'mileage' => $this->faker->numberBetween(5000, 250000),
        ];
    }
}
