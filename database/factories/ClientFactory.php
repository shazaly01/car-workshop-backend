<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'name' => 'John Doe'
            'name' => $this->faker->name(),

            // 'phone' => '555-123-4567' (يجب أن يكون فريدًا)
            'phone' => $this->faker->unique()->phoneNumber(),

            // 'email' => 'john.doe@example.com' (يجب أن يكون فريدًا)
            'email' => $this->faker->unique()->safeEmail(),

            // 'address' => '123 Main St, Anytown, USA'
            'address' => $this->faker->address(),

            // 'client_type' => 'individual' or 'company'
            // array_rand يختار قيمة عشوائية من المصفوفة
            'client_type' => $this->faker->randomElement(['individual', 'company']),
        ];
    }
}
