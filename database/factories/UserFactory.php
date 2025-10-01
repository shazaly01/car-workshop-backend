<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role; // <-- استيراد نموذج Role

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * --- الجزء الجديد والمهم ---
     *
     * تكوين الـ Factory لتعيين دور محدد للمستخدم بعد إنشائه.
     *
     * @param string $roleName اسم الدور (مثال: 'admin', 'technician')
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withRole(string $roleName): Factory
    {
        // afterCreating هي دالة خاصة في الـ Factories يتم تنفيذها بعد إنشاء النموذج
        return $this->afterCreating(function (User $user) use ($roleName) {
            // ابحث عن الدور، وإذا لم يكن موجودًا، قم بإنشائه
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
            $user->assignRole($role);
        });
    }

    /**
     * تكوين الـ Factory لتعيين صلاحية محددة للمستخدم بعد إنشائه.
     *
     * @param string $permissionName اسم الصلاحية
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withPermission(string $permissionName): Factory
    {
        return $this->afterCreating(function (User $user) use ($permissionName) {
            $user->givePermissionTo($permissionName);
        });
    }
}
