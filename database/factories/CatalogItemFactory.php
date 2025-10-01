<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CatalogItem>
 */
class CatalogItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // تحديد ما إذا كان الصنف خدمة أم قطعة غيار بشكل عشوائي
        $type = $this->faker->randomElement(['part', 'service']);
        $name = $type === 'part' ? 'Part: ' . $this->faker->words(3, true) : 'Service: ' . $this->faker->words(3, true);

        return [
            'sku' => $this->faker->unique()->bothify('??-####'), // مثال: AB-1234
            'name' => $name,
            'description' => $this->faker->sentence(),
            'type' => $type,
            'unit_price' => $this->faker->randomFloat(2, 10, 500), // رقم عشري بين 10 و 500
            'is_active' => true,
        ];
    }
}
