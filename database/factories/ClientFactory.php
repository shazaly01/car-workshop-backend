<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
// 1. استيراد مولّد Faker
use Faker\Factory as FakerFactory;

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
        // 2. إنشاء نسخة Faker باللغة العربية (المملكة العربية السعودية)
        $fakerAr = FakerFactory::create('ar_SA');

        return [
            // 3. استخدام النسخة العربية لتوليد البيانات
            'name' => $fakerAr->name(),
            'phone' => $fakerAr->unique()->phoneNumber(), // أرقام الهواتف ستكون بصيغة محلية
            'email' => $this->faker->unique()->safeEmail(), // يمكن إبقاء البريد الإلكتروني كما هو
            'address' => $fakerAr->address(), // سيتم توليد عنوان باللغة العربية
            'client_type' => $this->faker->randomElement(['individual', 'company']),
        ];
    }
}
